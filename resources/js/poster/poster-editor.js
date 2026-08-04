/*
 | BKD Afiş Motoru — BkdTextFrame
 | --------------------------------------------------------------------------
 | Her yazı katmanı seçilebilir çerçeve + Textbox'dur.
 | Metin yalnızca kelime sınırlarında alt satıra geçer; punto binary-search
 | ile kutuya sığdırılır (harf bazlı bölme yok).
 |
 | Modlar: design | studio | generate
 */

const GOOGLE_FONTS = [
    'Inter', 'Lora', 'Montserrat', 'Roboto', 'Open+Sans',
    'Poppins', 'Playfair+Display', 'Merriweather', 'Oswald',
];

/** Uzun metin önizlemesi gereken bağlantılar (şablon tasarımı) */
const LONG_PREVIEW_BINDINGS = ['not', 'aciklama', 'tesekkur_metni'];

const LONG_PREVIEW_SAMPLES = {
    not: 'Bu 5000 TL SECDER Derneği yani Selahaddin Eyyubi Cami Derneğine cami adına kullanılması için bağışlanmıştır.',
    aciklama: 'Bu 5000 TL SECDER Derneği yani Selahaddin Eyyubi Cami Derneğine cami adına kullanılması için bağışlanmıştır.',
    tesekkur_metni: '5.000,00 TRY tutarındaki Genel Bağış bağışınız 04.08.2026 tarihinde derneğimize ulaşmış ve kayda alınmıştır.\n\nCami Bağışı kapsamında verdiğiniz destek, yürüttüğümüz hizmetlere güç katmaktadır.\n\nÖdeme türü: Banka Havalesi / EFT.\n\nBağış No: BAG-2026-00001  |  Makbuz No: MKB-2026-00001',
    faaliyet: 'Cami Bağışı',
    bagis_turu: 'Genel Bağış',
    odeme_turu: 'Banka Havalesi / EFT',
    tutar_birimli: '5.000,00 TRY',
    bagis_tutari: '5.000,00',
    para_birimi: 'TRY',
    tarih: '04.08.2026',
    bagis_no: 'BAG-2026-00001',
    makbuz_no: 'MKB-2026-00001',
    ad_soyad: 'Burak Gül',
    ad: 'Burak',
    soyad: 'Gül',
    telefon: '05426588530',
    dernek_adi: 'SECDER',
};

const MIN_FONT_SIZE = 8;

/** Fabric ölçüm yuvarlama payı (px). */
const FIT_TOLERANCE = 3;

/** Binary search son çare minimum punto. */
const ABSOLUTE_MIN_FONT_SIZE = 6;

let fontsInjected = false;

function injectGoogleFonts() {
    if (fontsInjected) {
        return;
    }
    fontsInjected = true;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://fonts.googleapis.com/css2?'
        + GOOGLE_FONTS.map((f) => `family=${f}:ital,wght@0,400;0,600;0,700;1,400`).join('&')
        + '&display=swap';
    document.head.appendChild(link);
}

async function ensureFontsLoaded(families) {
    injectGoogleFonts();
    try {
        await document.fonts.ready;
        await Promise.all(
            (families || []).map((f) => document.fonts.load(`700 24px "${f}"`).catch(() => {})),
        );
        await document.fonts.ready;
    } catch (e) {
        /* yoksay */
    }
}

async function ensureFontForText(text, size) {
    injectGoogleFonts();
    const family = text.fontFamily || 'Inter';
    const weight = text.fontWeight === 'bold' ? '700' : '400';
    const px = size || text.bkdDesiredFontSize || text.fontSize || 24;
    try {
        await document.fonts.load(`${weight} ${px}px "${family}"`);
        await document.fonts.load(`italic ${weight} ${px}px "${family}"`).catch(() => {});
        await document.fonts.ready;
    } catch (e) {
        /* yoksay */
    }
}

let fabricModulePromise = null;
function loadFabric() {
    if (!fabricModulePromise) {
        fabricModulePromise = import('fabric');
    }
    return fabricModulePromise;
}

function dataUrlToBlob(dataUrl) {
    const [meta, b64] = dataUrl.split(',');
    const mime = /:(.*?);/.exec(meta)?.[1] || 'image/png';
    const bin = atob(b64);
    const bytes = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i += 1) {
        bytes[i] = bin.charCodeAt(i);
    }
    return new Blob([bytes], { type: mime });
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function padOf(width) {
    return Math.min(30, Math.max(6, Math.round(width * 0.02)));
}

/** Aynı origin için crossOrigin gönderme; farklı origin'de canvas export için anonymous. */
function imageLoadOptions(url) {
    try {
        const resolved = new URL(url, window.location.origin);
        if (resolved.origin !== window.location.origin) {
            return { crossOrigin: 'anonymous' };
        }
    } catch (e) {
        /* yoksay */
    }
    return {};
}

function maxLineWidth(text) {
    try {
        const n = text.textLines ? text.textLines.length : 0;
        let max = 0;
        for (let i = 0; i < n; i += 1) {
            const lw = text.getLineWidth(i);
            if (lw > max) {
                max = lw;
            }
        }
        return max || text.width;
    } catch (e) {
        return text.width;
    }
}

/** Canvas 2D ile kelime genişliği ölçer (Fabric ile uyumlu font dizesi). */
function buildWordMeasurer(text) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const family = text.fontFamily || 'Inter';
    const weight = text.fontWeight === 'bold' ? 'bold' : 'normal';
    const style = text.fontStyle === 'italic' ? 'italic' : 'normal';

    return (str, fontSize) => {
        ctx.font = `${style} ${weight} ${fontSize}px "${family}"`;
        return ctx.measureText(str).width;
    };
}

/**
 * Metni yalnızca kelime sınırlarında sarar. Tek kelime kutudan genişse null döner.
 *
 * @param {string} source
 * @param {number} maxWidth
 * @param {(s: string) => number} measureFn
 * @returns {string[] | null}
 */
function wordWrapAtSpaces(source, maxWidth, measureFn) {
    const usable = Math.max(10, maxWidth - FIT_TOLERANCE);
    const paragraphs = String(source).replace(/\r\n/g, '\n').split('\n');
    const lines = [];

    for (const paragraph of paragraphs) {
        const trimmed = paragraph.trim();
        if (!trimmed) {
            lines.push('');
            continue;
        }

        const words = trimmed.split(/\s+/);
        let current = '';

        for (const word of words) {
            if (measureFn(word) > usable) {
                return null;
            }

            const candidate = current ? `${current} ${word}` : word;
            if (measureFn(candidate) <= usable) {
                current = candidate;
            } else {
                lines.push(current);
                current = word;
            }
        }

        if (current) {
            lines.push(current);
        }
    }

    return lines.length ? lines : [''];
}

function blockHeight(lineCount, fontSize, lineHeight) {
    if (lineCount <= 0) {
        return 0;
    }
    return lineCount * fontSize * lineHeight;
}

/**
 * Kelime sarmalı + binary search: kutuya sığan en büyük puntoyu bulur.
 *
 * @returns {{ size: number, lines: string[], lineHeight: number, fits: boolean }}
 */
function fitWordWrappedText(source, innerW, innerH, textStyle, desiredSize, minSize, lineHeight) {
    const measureAt = buildWordMeasurer(textStyle);

    const trySize = (size) => {
        const measure = (s) => measureAt(s, size);
        const lines = wordWrapAtSpaces(source, innerW, measure);
        if (!lines) {
            return null;
        }
        const h = blockHeight(lines.length, size, lineHeight);
        if (h > innerH + FIT_TOLERANCE) {
            return null;
        }
        return { size, lines, lineHeight, fits: true };
    };

    let lo = minSize;
    let hi = Math.max(minSize, desiredSize);
    let best = trySize(minSize);

    if (trySize(hi)) {
        return trySize(hi);
    }

    while (lo <= hi) {
        const mid = Math.floor((lo + hi) / 2);
        const attempt = trySize(mid);
        if (attempt) {
            best = attempt;
            lo = mid + 1;
        } else {
            hi = mid - 1;
        }
    }

    if (best?.fits) {
        return best;
    }

    const fallbackLines = wordWrapAtSpaces(source, innerW, (s) => measureAt(s, minSize)) || [source];
    return {
        size: minSize,
        lines: fallbackLines,
        lineHeight,
        fits: false,
    };
}

/**
 * Fabric Textbox genişliğini sabitler (otomatik genişlemeyi kapatır).
 */
function lockTextWidth(text, innerW) {
    if ('dynamicMinWidth' in text) {
        text.dynamicMinWidth = 0;
    }
    text.set({ width: innerW, splitByGrapheme: false });
    if (typeof text.initDimensions === 'function') {
        text.initDimensions();
    }
    if ('dynamicMinWidth' in text) {
        text.dynamicMinWidth = 0;
    }
    if (Math.abs(text.width - innerW) > 0.5) {
        text.set('width', innerW);
    }
}

function textOverflows(text, innerW, innerH) {
    lockTextWidth(text, innerW);
    return text.height > innerH + FIT_TOLERANCE
        || maxLineWidth(text) > innerW + FIT_TOLERANCE;
}

function applyWordWrapResult(text, innerW, result) {
    text.set({
        text: result.lines.join('\n'),
        fontSize: result.size,
        lineHeight: result.lineHeight,
        width: innerW,
        splitByGrapheme: false,
    });
    lockTextWidth(text, innerW);
}

/* ---------------------------------------------------------------------------
 | PosterCanvas — BkdTextFrame yönetimi
 | ------------------------------------------------------------------------- */

class PosterCanvas {
    constructor(fabric, canvasEl, config) {
        this.fabric = fabric;
        this.config = config;
        this.mode = config.mode || 'document';
        this.naturalW = config.canvasWidth || 0;
        this.naturalH = config.canvasHeight || 0;
        this.scale = 1;
        /** @type {import('fabric').Rect[]} */
        this.frames = [];
        this.longPreview = config.longPreview !== false;

        this.canvas = new fabric.Canvas(canvasEl, {
            preserveObjectStacking: true,
            backgroundColor: '#ffffff',
            enableRetinaScaling: false,
            selection: false,
        });
    }

    async init(displayWidth) {
        this.frames = [];

        let bgImg = null;
        if (this.config.backgroundUrl) {
            try {
                bgImg = await this.fabric.FabricImage.fromURL(
                    this.config.backgroundUrl,
                    imageLoadOptions(this.config.backgroundUrl),
                );
                if (!this.naturalW || !this.naturalH) {
                    this.naturalW = bgImg.width;
                    this.naturalH = bgImg.height;
                }
            } catch (e) {
                console.error('Afiş arka planı yüklenemedi:', this.config.backgroundUrl, e);
                bgImg = null;
            }
        }

        if (!this.naturalW || !this.naturalH) {
            this.naturalW = 720;
            this.naturalH = 1080;
        }

        this.applyDisplaySize(displayWidth);

        if (bgImg) {
            bgImg.set({
                left: 0, top: 0, originX: 'left', originY: 'top',
                selectable: false, evented: false, scaleX: 1, scaleY: 1,
            });
            this.canvas.backgroundImage = bgImg;
        }

        for (const layer of (this.config.layout || [])) {
            // eslint-disable-next-line no-await-in-loop
            await this.addLayer(layer, false);
        }

        await ensureFontsLoaded(this.usedFonts());
        for (const frame of this.frames) {
            // eslint-disable-next-line no-await-in-loop
            await this.refit(frame);
        }
        this.canvas.requestRenderAll();
    }

    applyDisplaySize(displayWidth) {
        const maxW = displayWidth || this.naturalW;
        this.scale = Math.min(1, maxW / this.naturalW);
        this.canvas.setDimensions({
            width: Math.round(this.naturalW * this.scale),
            height: Math.round(this.naturalH * this.scale),
        });
        this.canvas.setZoom(this.scale);
    }

    async setLongPreview(enabled) {
        this.longPreview = !!enabled;
        if (this.mode !== 'design') {
            return;
        }
        for (const frame of this.frames) {
            const text = frame.bkdText;
            if (!text?.bkdBinding) {
                continue;
            }
            text.set('text', this.previewTextForBinding(text.bkdBinding));
            // eslint-disable-next-line no-await-in-loop
            await this.refit(frame);
        }
        this.canvas.requestRenderAll();
    }

    previewTextForBinding(binding) {
        if (this.longPreview && LONG_PREVIEW_BINDINGS.includes(binding)) {
            return LONG_PREVIEW_SAMPLES[binding] || `{${binding}}`;
        }
        return `{${binding}}`;
    }

    resolveText(layer) {
        if (this.mode === 'design') {
            if (layer.binding) {
                return this.previewTextForBinding(layer.binding);
            }
            return layer.text ?? 'Metin';
        }
        if (this.mode === 'studio') {
            return layer.text ?? '';
        }
        if (layer.binding) {
            const value = (this.config.data || {})[layer.binding];
            return value === undefined || value === null ? '' : String(value);
        }
        return layer.text ?? '';
    }

    /**
     * BkdTextFrame oluşturur: seçilebilir çerçeve (Rect) + kelime sarmalı yazı (Textbox).
     */
    async addLayer(layer, select = true) {
        const fabric = this.fabric;
        const isGen = this.mode === 'generate';
        const desiredSize = layer.desiredFontSize ?? layer.fontSize ?? 42;
        const rawContent = this.resolveText(layer) || ' ';

        const bx = layer.left ?? Math.round(this.naturalW * 0.15);
        const by = layer.top ?? Math.round(this.naturalH * 0.4);
        const bw = layer.width ?? Math.round(this.naturalW * 0.7);

        const text = new fabric.Textbox(rawContent, {
            fontSize: desiredSize,
            fontFamily: layer.fontFamily ?? 'Inter',
            fill: layer.fill ?? '#3f4c6b',
            fontWeight: layer.fontWeight ?? 'normal',
            fontStyle: layer.fontStyle ?? 'normal',
            underline: layer.underline ?? false,
            textAlign: layer.textAlign ?? 'center',
            lineHeight: layer.lineHeight ?? 1.16,
            originX: 'left',
            originY: 'top',
            selectable: false,
            evented: false,
            editable: false,
            splitByGrapheme: false,
        });
        text.bkdBinding = layer.binding ?? null;
        text.bkdDesiredFontSize = desiredSize;
        text.bkdSourceText = rawContent;

        const pad = padOf(bw);
        text.set('width', Math.max(20, bw - 2 * pad));
        if (typeof text.initDimensions === 'function') {
            text.initDimensions();
        }

        let bh = layer.height;
        if (!bh || bh < 10) {
            bh = Math.round(text.height + 2 * pad + 8);
            bh = Math.max(bh, Math.round(this.naturalH * 0.08));
        }

        const frame = new fabric.Rect({
            left: bx,
            top: by,
            width: bw,
            height: bh,
            fill: 'rgba(0,0,0,0)',
            stroke: isGen ? 'rgba(0,0,0,0)' : '#5f6f9b',
            strokeDashArray: isGen ? null : [6, 4],
            strokeWidth: isGen ? 0 : 1,
            strokeUniform: true,
            originX: 'left',
            originY: 'top',
            selectable: !isGen,
            evented: !isGen,
            lockRotation: true,
            objectCaching: false,
            hasBorders: true,
        });
        frame.bkdRole = 'frame';
        frame.bkdText = text;
        frame.bkdValign = layer.valign ?? 'top';
        frame.bkdDesiredFontSize = desiredSize;
        if (!isGen) {
            frame.setControlsVisibility({ mtr: false });
        }

        this.canvas.add(frame);
        this.canvas.add(text);
        this.frames.push(frame);
        await this.refit(frame);

        if (select && !isGen) {
            this.canvas.setActiveObject(frame);
        }
        return frame;
    }

    frameDimensions(frame) {
        const w = Math.max(20, frame.width * (frame.scaleX || 1));
        const h = Math.max(20, frame.height * (frame.scaleY || 1));
        return { w, h, left: frame.left, top: frame.top };
    }

    /**
     * Yazıyı kutuya kelime sınırlarında sarar; punto binary-search ile sığdırır.
     */
    async refit(frame) {
        const text = frame?.bkdText;
        if (!text) {
            return;
        }

        const { w, h, left, top } = this.frameDimensions(frame);
        const pad = padOf(w);
        const innerW = Math.max(20, w - 2 * pad);
        const innerH = Math.max(20, h - 2 * pad);
        const desired = text.bkdDesiredFontSize || frame.bkdDesiredFontSize || text.fontSize;
        const baseLineHeight = text.bkdBaseLineHeight ?? text.lineHeight ?? 1.16;
        if (text.bkdBaseLineHeight == null) {
            text.bkdBaseLineHeight = baseLineHeight;
        }

        const source = text.bkdBinding
            ? this.resolveText({ binding: text.bkdBinding })
            : (text.bkdSourceText ?? String(text.text || '').replace(/\n/g, ' '));
        text.bkdSourceText = source;

        await ensureFontForText(text, desired);

        const lineHeights = [
            baseLineHeight,
            Math.max(1.0, baseLineHeight - 0.12),
            Math.max(0.95, baseLineHeight - 0.2),
        ];

        let result = null;
        for (const lh of lineHeights) {
            result = fitWordWrappedText(source, innerW, innerH, text, desired, MIN_FONT_SIZE, lh);
            applyWordWrapResult(text, innerW, result);
            if (result.fits && !textOverflows(text, innerW, innerH)) {
                break;
            }
        }

        if (!result?.fits || textOverflows(text, innerW, innerH)) {
            result = fitWordWrappedText(
                source,
                innerW,
                innerH,
                text,
                desired,
                ABSOLUTE_MIN_FONT_SIZE,
                lineHeights[lineHeights.length - 1],
            );
            applyWordWrapResult(text, innerW, result);
        }

        let guard = result.size;
        while (guard > ABSOLUTE_MIN_FONT_SIZE && textOverflows(text, innerW, innerH)) {
            guard -= 1;
            result = fitWordWrappedText(
                source,
                innerW,
                innerH,
                text,
                guard,
                guard,
                result.lineHeight,
            );
            applyWordWrapResult(text, innerW, result);
        }

        text.bkdFittedFontSize = result.size;
        frame.bkdDesiredFontSize = desired;
        frame.bkdFitWarning = !result.fits || textOverflows(text, innerW, innerH);

        const th = text.height;
        let ty = top + pad;
        if (frame.bkdValign === 'middle') {
            ty = top + (h - th) / 2;
        } else if (frame.bkdValign === 'bottom') {
            ty = top + h - pad - th;
        }

        text.set({ left: left + pad, top: ty });
        text.setCoords();
        text.clipPath = null;
    }

    normalizeFrame(frame) {
        if ((frame.scaleX && frame.scaleX !== 1) || (frame.scaleY && frame.scaleY !== 1)) {
            frame.width = Math.max(20, frame.width * frame.scaleX);
            frame.height = Math.max(20, frame.height * frame.scaleY);
            frame.scaleX = 1;
            frame.scaleY = 1;
            frame.setCoords();
        }
    }

    setFrameSize(frame, width, height) {
        this.normalizeFrame(frame);
        frame.set({
            width: Math.max(40, Math.round(width)),
            height: Math.max(30, Math.round(height)),
        });
        frame.setCoords();
    }

    usedFonts() {
        const set = new Set(['Inter']);
        this.frames.forEach((frame) => {
            if (frame.bkdText?.fontFamily) {
                set.add(frame.bkdText.fontFamily);
            }
        });
        return [...set];
    }

    removeFrame(frame) {
        this.canvas.remove(frame.bkdText);
        this.canvas.remove(frame);
        this.frames = this.frames.filter((f) => f !== frame);
    }

    serialize(bakeText = false) {
        return this.frames.map((frame) => {
            this.normalizeFrame(frame);
            const t = frame.bkdText;
            const desired = frame.bkdDesiredFontSize ?? t.bkdDesiredFontSize ?? t.fontSize;
            return {
                type: 'text',
                binding: t.bkdBinding ?? null,
                text: t.bkdBinding && !bakeText ? '' : t.text,
                left: Math.round(frame.left),
                top: Math.round(frame.top),
                width: Math.round(frame.width),
                height: Math.round(frame.height),
                fontSize: Math.round(desired),
                desiredFontSize: Math.round(desired),
                fittedFontSize: Math.round(t.bkdFittedFontSize ?? t.fontSize),
                fontFamily: t.fontFamily,
                fill: t.fill,
                fontWeight: t.fontWeight,
                fontStyle: t.fontStyle,
                underline: !!t.underline,
                textAlign: t.textAlign,
                lineHeight: t.lineHeight,
                angle: 0,
                valign: frame.bkdValign || 'top',
            };
        });
    }

    async exportDataUrl() {
        await ensureFontsLoaded(this.usedFonts());
        for (const frame of this.frames) {
            this.normalizeFrame(frame);
            // eslint-disable-next-line no-await-in-loop
            await this.refit(frame);
        }
        this.canvas.discardActiveObject();

        const hidden = [];
        this.frames.forEach((frame) => {
            if (frame.visible) {
                hidden.push(frame);
                frame.visible = false;
            }
        });
        this.canvas.requestRenderAll();

        const url = this.canvas.toDataURL({
            format: 'png',
            multiplier: this.scale ? 1 / this.scale : 1,
        });

        hidden.forEach((frame) => { frame.visible = true; });
        this.canvas.requestRenderAll();

        return url;
    }

    dispose() {
        try {
            this.canvas.dispose();
        } catch (e) {
            /* yoksay */
        }
    }
}

/* ---------------------------------------------------------------------------
 | UI
 | ------------------------------------------------------------------------- */

function el(tag, attrs = {}, children = []) {
    const node = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => {
        if (k === 'class') node.className = v;
        else if (k === 'style') node.setAttribute('style', v);
        else if (k.startsWith('on') && typeof v === 'function') node.addEventListener(k.slice(2), v);
        else if (v !== null && v !== undefined) node.setAttribute(k, v);
    });
    (Array.isArray(children) ? children : [children]).forEach((c) => {
        if (c == null) return;
        node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return node;
}

const BTN = 'display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-weight:600;font-size:13px;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.05);';
const BTN_ACTIVE = BTN + 'background:#e8edf6;border-color:#8397bd;color:#3f4c6b;';
const BTN_PRIMARY = 'display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;border:1px solid #4d5c83;background:#4d5c83;color:#fff;font-weight:700;font-size:13px;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,.08);';
const FIELD = 'width:100%;padding:7px 9px;border-radius:8px;border:1px solid #cbd5e1;font-size:13px;background:#fff;color:#0f172a;';
const LABEL = 'display:block;font-size:12px;font-weight:600;color:#475569;margin:10px 0 4px;';
const HINT = 'font-size:11px;color:#64748b;margin-top:4px;line-height:1.4;';

function initEditor(root) {
    if (root.dataset.posterInit === '1') {
        return;
    }
    root.dataset.posterInit = '1';

    const configEl = root.querySelector('[data-poster-config]');
    if (!configEl) {
        return;
    }

    let config;
    try {
        config = JSON.parse(configEl.textContent);
    } catch (e) {
        return;
    }

    const stage = root.querySelector('[data-poster-stage]');
    const toolbar = root.querySelector('[data-poster-toolbar]');
    const propsPanel = root.querySelector('[data-poster-props]');
    if (!stage || !toolbar || !propsPanel) {
        return;
    }

    const canvasEl = document.createElement('canvas');
    stage.appendChild(canvasEl);

    loadFabric().then(async (fabric) => {
        const displayWidth = stage.clientWidth || 700;
        const pc = new PosterCanvas(fabric, canvasEl, config);
        await pc.init(displayWidth);

        buildToolbar(toolbar, pc, config);
        const refreshProps = buildPropsPanel(propsPanel, pc, config);

        pc.canvas.on('selection:created', refreshProps);
        pc.canvas.on('selection:updated', refreshProps);
        pc.canvas.on('selection:cleared', refreshProps);

        pc.canvas.on('object:moving', (e) => {
            if (e.target?.bkdRole === 'frame') {
                pc.refit(e.target);
            }
        });
        pc.canvas.on('object:scaling', (e) => {
            if (e.target?.bkdRole === 'frame') {
                pc.refit(e.target);
            }
        });
        pc.canvas.on('object:modified', async (e) => {
            if (e.target?.bkdRole === 'frame') {
                pc.normalizeFrame(e.target);
                await pc.refit(e.target);
                pc.canvas.requestRenderAll();
                refreshProps();
            }
        });

        refreshProps();

        window.addEventListener('resize', () => {
            const w = stage.clientWidth || displayWidth;
            pc.applyDisplaySize(w);
            pc.canvas.requestRenderAll();
        });
    });
}

function buildToolbar(toolbar, pc, config) {
    toolbar.innerHTML = '';

    const addBtn = el('button', { type: 'button', style: BTN, onclick: async () => {
        await pc.addLayer({
            binding: null,
            text: 'Yeni metin',
            left: Math.round(pc.naturalW * 0.2),
            top: Math.round(pc.naturalH * 0.4),
            width: Math.round(pc.naturalW * 0.6),
            height: Math.round(pc.naturalH * 0.12),
            fontSize: 44,
            fontFamily: 'Inter',
            fill: '#3f4c6b',
            textAlign: 'center',
            valign: 'middle',
        });
        pc.canvas.requestRenderAll();
    } }, '+ Yazı kutusu ekle');
    toolbar.appendChild(addBtn);

    if (config.mode === 'design') {
        let previewOn = pc.longPreview;
        const previewBtn = el('button', {
            type: 'button',
            style: previewOn ? BTN_ACTIVE : BTN,
            onclick: async () => {
                previewOn = !previewOn;
                previewBtn.setAttribute('style', previewOn ? BTN_ACTIVE : BTN);
                await pc.setLongPreview(previewOn);
            },
        }, 'Uzun metin önizlemesi');
        toolbar.appendChild(previewBtn);

        const saveBtn = el('button', { type: 'button', style: BTN_PRIMARY, onclick: () => {
            const layout = pc.serialize(false);
            window.dispatchEvent(new CustomEvent('poster-save', {
                detail: { layout, width: pc.naturalW, height: pc.naturalH },
            }));
        } }, 'Şablonu kaydet');
        toolbar.appendChild(saveBtn);
    }

    if (config.mode === 'studio') {
        const saveBtn = el('button', { type: 'button', style: BTN_PRIMARY, onclick: async (e) => {
            const btn = e.currentTarget;
            btn.disabled = true;
            btn.textContent = 'Kaydediliyor...';
            try {
                const dataUrl = await pc.exportDataUrl();
                const fd = new FormData();
                fd.append('layout_snapshot', JSON.stringify(pc.serialize(true)));
                fd.append('image', dataUrlToBlob(dataUrl), 'poster.png');
                const res = await fetch(config.saveUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
                    body: fd,
                });
                if (!res.ok) throw new Error('Kaydedilemedi');
                if (config.returnUrl) {
                    window.location.href = config.returnUrl;
                } else {
                    btn.textContent = 'Kaydedildi';
                }
            } catch (err) {
                btn.disabled = false;
                btn.textContent = 'Kaydet';
                alert('Afiş kaydedilemedi: ' + err.message);
            }
        } }, 'Kaydet');
        toolbar.appendChild(saveBtn);

        if (config.returnUrl) {
            toolbar.appendChild(el('a', { href: config.returnUrl, style: BTN }, 'Geri dön'));
        }
    }
}

function buildPropsPanel(panel, pc, config) {
    const placeholders = config.placeholders || {};
    const fonts = config.fonts || ['Inter'];

    const empty = el('div', { style: 'color:#64748b;font-size:13px;padding:8px 0;line-height:1.5;' },
        'Bir yazı kutusuna tıklayın. Kutuyu sürükleyerek veya sağ panelden genişlik/yükseklik girerek alanı belirleyin. Yazı bu alana otomatik sığar ve dışarı taşmaz.');

    const wrap = el('div', { style: 'display:none;' });

    let bindingSel = null;
    if (config.mode === 'design') {
        const opts = [el('option', { value: '' }, 'Statik metin (sabit)')];
        Object.entries(placeholders).forEach(([key, label]) => {
            opts.push(el('option', { value: key }, `${label}  {${key}}`));
        });
        bindingSel = el('select', { style: FIELD }, opts);
        wrap.appendChild(el('label', { style: LABEL }, 'İçerik kaynağı'));
        wrap.appendChild(bindingSel);
    }

    const textArea = el('textarea', { style: FIELD + 'min-height:64px;resize:vertical;' });
    wrap.appendChild(el('label', { style: LABEL }, 'Metin'));
    wrap.appendChild(textArea);

    const boxSizeRow = el('div', { style: 'display:flex;gap:8px;' });
    const widthInput = el('input', { type: 'number', min: '40', style: FIELD });
    const heightInput = el('input', { type: 'number', min: '30', style: FIELD });
    boxSizeRow.append(
        el('div', { style: 'flex:1;' }, [
            el('label', { style: LABEL + 'margin-top:0;' }, 'Kutu genişliği (px)'),
            widthInput,
        ]),
        el('div', { style: 'flex:1;' }, [
            el('label', { style: LABEL + 'margin-top:0;' }, 'Kutu yüksekliği (px)'),
            heightInput,
        ]),
    );
    wrap.appendChild(boxSizeRow);
    wrap.appendChild(el('div', { style: HINT }, 'Kutuyu tuvalde sürükleyerek de boyutlandırabilirsiniz.'));

    const fontSel = el('select', { style: FIELD }, fonts.map((f) => el('option', { value: f }, f)));
    wrap.appendChild(el('label', { style: LABEL }, 'Yazı tipi'));
    wrap.appendChild(fontSel);

    const sizeInput = el('input', { type: 'number', min: '6', max: '400', style: FIELD });
    wrap.appendChild(el('label', { style: LABEL }, 'İstenen yazı boyutu (px)'));
    wrap.appendChild(sizeInput);
    wrap.appendChild(el('div', { style: HINT }, 'Yazı yalnızca kelime sınırlarında alt satıra geçer; kutuya sığmazsa punto otomatik küçülür.'));

    const fittedHint = el('div', { style: HINT + 'color:#3f4c6b;' }, '');
    wrap.appendChild(fittedHint);

    const colorInput = el('input', { type: 'color', style: 'width:100%;height:38px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;cursor:pointer;' });
    wrap.appendChild(el('label', { style: LABEL }, 'Renk'));
    wrap.appendChild(colorInput);

    const styleRow = el('div', { style: 'display:flex;gap:6px;margin-top:8px;' });
    const boldBtn = el('button', { type: 'button', style: BTN + 'font-weight:800;flex:1;' }, 'K');
    const italicBtn = el('button', { type: 'button', style: BTN + 'font-style:italic;flex:1;' }, 'İ');
    const underlineBtn = el('button', { type: 'button', style: BTN + 'text-decoration:underline;flex:1;' }, 'A');
    styleRow.append(boldBtn, italicBtn, underlineBtn);
    wrap.appendChild(el('label', { style: LABEL }, 'Stil'));
    wrap.appendChild(styleRow);

    const alignRow = el('div', { style: 'display:flex;gap:6px;margin-top:8px;' });
    const alignBtns = ['left', 'center', 'right'].map((a) =>
        el('button', { type: 'button', 'data-align': a, style: BTN + 'flex:1;' },
            a === 'left' ? 'Sol' : a === 'center' ? 'Orta' : 'Sağ'));
    alignBtns.forEach((b) => alignRow.appendChild(b));
    wrap.appendChild(el('label', { style: LABEL }, 'Yatay hizalama'));
    wrap.appendChild(alignRow);

    const valignRow = el('div', { style: 'display:flex;gap:6px;margin-top:8px;' });
    const valignBtns = ['top', 'middle', 'bottom'].map((a) =>
        el('button', { type: 'button', 'data-valign': a, style: BTN + 'flex:1;' },
            a === 'top' ? 'Üst' : a === 'middle' ? 'Orta' : 'Alt'));
    valignBtns.forEach((b) => valignRow.appendChild(b));
    wrap.appendChild(el('label', { style: LABEL }, 'Dikey hizalama'));
    wrap.appendChild(valignRow);

    const lineInput = el('input', { type: 'number', min: '0.8', max: '3', step: '0.05', style: FIELD });
    wrap.appendChild(el('label', { style: LABEL }, 'Satır aralığı'));
    wrap.appendChild(lineInput);

    const delBtn = el('button', { type: 'button', style: BTN + 'margin-top:14px;width:100%;border-color:#fecaca;color:#b91c1c;background:#fef2f2;' }, 'Kutuyu sil');
    wrap.appendChild(delBtn);

    panel.innerHTML = '';
    panel.append(empty, wrap);

    const getActive = () => {
        const o = pc.canvas.getActiveObject();
        return o && o.bkdRole === 'frame' ? o : null;
    };

    function refresh() {
        const frame = getActive();
        if (!frame) {
            wrap.style.display = 'none';
            empty.style.display = 'block';
            return;
        }
        const t = frame.bkdText;
        empty.style.display = 'none';
        wrap.style.display = 'block';

        pc.normalizeFrame(frame);
        widthInput.value = Math.round(frame.width);
        heightInput.value = Math.round(frame.height);

        if (bindingSel) {
            bindingSel.value = t.bkdBinding || '';
            textArea.disabled = !!t.bkdBinding;
            textArea.value = t.bkdBinding
                ? (pc.longPreview && LONG_PREVIEW_BINDINGS.includes(t.bkdBinding)
                    ? pc.previewTextForBinding(t.bkdBinding)
                    : `{${t.bkdBinding}}`)
                : (t.text || '');
        } else {
            textArea.value = t.text || '';
        }
        fontSel.value = t.fontFamily || 'Inter';
        const desired = frame.bkdDesiredFontSize ?? t.bkdDesiredFontSize ?? t.fontSize;
        sizeInput.value = Math.round(desired);
        const fitted = t.bkdFittedFontSize ?? t.fontSize;
        if (frame.bkdFitWarning) {
            fittedHint.textContent = 'Metin kutuya sığmıyor — kutu yüksekliğini artırın veya istenen puntoyu küçültün.';
            fittedHint.style.color = '#b45309';
        } else {
            fittedHint.style.color = '#3f4c6b';
            fittedHint.textContent = fitted < desired
                ? `Kutuya sığan gerçek boyut: ${Math.round(fitted)} px`
                : '';
        }
        colorInput.value = toHex(t.fill);
        lineInput.value = t.lineHeight ?? 1.16;
        boldBtn.style.background = t.fontWeight === 'bold' ? '#e2e8f0' : '#fff';
        italicBtn.style.background = t.fontStyle === 'italic' ? '#e2e8f0' : '#fff';
        underlineBtn.style.background = t.underline ? '#e2e8f0' : '#fff';
        alignBtns.forEach((b) => {
            b.style.background = b.getAttribute('data-align') === t.textAlign ? '#e2e8f0' : '#fff';
        });
        valignBtns.forEach((b) => {
            b.style.background = b.getAttribute('data-valign') === (frame.bkdValign || 'top') ? '#e2e8f0' : '#fff';
        });
    }

    const apply = async (fn) => {
        const frame = getActive();
        if (!frame) return;
        fn(frame.bkdText, frame);
        await pc.refit(frame);
        pc.canvas.requestRenderAll();
        refresh();
    };

    if (bindingSel) {
        bindingSel.addEventListener('change', () => {
            apply((t, frame) => {
                const val = bindingSel.value || null;
                t.bkdBinding = val;
                const sample = val ? pc.previewTextForBinding(val) : 'Metin';
                t.bkdSourceText = sample;
                t.set('text', sample);
                frame.bkdDesiredFontSize = t.bkdDesiredFontSize;
            });
        });
    }
    textArea.addEventListener('input', () => apply((t) => {
        if (t.bkdBinding) return;
        t.bkdSourceText = textArea.value;
        t.set('text', textArea.value);
    }));
    widthInput.addEventListener('change', () => apply((t, frame) => {
        pc.setFrameSize(frame, parseInt(widthInput.value, 10) || frame.width, frame.height);
    }));
    heightInput.addEventListener('change', () => apply((t, frame) => {
        pc.setFrameSize(frame, frame.width, parseInt(heightInput.value, 10) || frame.height);
    }));
    fontSel.addEventListener('change', () => apply((t) => t.set('fontFamily', fontSel.value)));
    sizeInput.addEventListener('input', () => apply((t, frame) => {
        const v = parseInt(sizeInput.value, 10) || 12;
        t.bkdDesiredFontSize = v;
        frame.bkdDesiredFontSize = v;
        t.set('fontSize', v);
    }));
    colorInput.addEventListener('input', () => apply((t) => t.set('fill', colorInput.value)));
    lineInput.addEventListener('input', () => apply((t) => {
        const lh = parseFloat(lineInput.value) || 1.16;
        t.bkdBaseLineHeight = lh;
        t.set('lineHeight', lh);
    }));
    boldBtn.addEventListener('click', () => { apply((t) => t.set('fontWeight', t.fontWeight === 'bold' ? 'normal' : 'bold')); });
    italicBtn.addEventListener('click', () => { apply((t) => t.set('fontStyle', t.fontStyle === 'italic' ? 'normal' : 'italic')); });
    underlineBtn.addEventListener('click', () => { apply((t) => t.set('underline', !t.underline)); });
    alignBtns.forEach((b) => b.addEventListener('click', () => {
        apply((t) => t.set('textAlign', b.getAttribute('data-align')));
    }));
    valignBtns.forEach((b) => b.addEventListener('click', () => {
        apply((t, frame) => { frame.bkdValign = b.getAttribute('data-valign'); });
    }));
    delBtn.addEventListener('click', () => {
        const frame = getActive();
        if (frame) {
            pc.removeFrame(frame);
            pc.canvas.discardActiveObject();
            pc.canvas.requestRenderAll();
            refresh();
        }
    });

    return refresh;
}

function toHex(fill) {
    if (typeof fill !== 'string') return '#3f4c6b';
    if (fill.startsWith('#')) {
        if (fill.length === 4) {
            return '#' + fill.slice(1).split('').map((c) => c + c).join('');
        }
        return fill.slice(0, 7);
    }
    const m = /rgba?\((\d+),\s*(\d+),\s*(\d+)/.exec(fill);
    if (m) {
        return '#' + [m[1], m[2], m[3]].map((n) => parseInt(n, 10).toString(16).padStart(2, '0')).join('');
    }
    return '#3f4c6b';
}

/* ---------------------------------------------------------------------------
 | Sessiz üretim
 | ------------------------------------------------------------------------- */

async function runGenerateJob(job) {
    const fabric = await loadFabric();
    const holder = el('div', { style: 'position:fixed;left:-99999px;top:0;width:1px;height:1px;overflow:hidden;' });
    const canvasEl = document.createElement('canvas');
    holder.appendChild(canvasEl);
    document.body.appendChild(holder);

    const pc = new PosterCanvas(fabric, canvasEl, { ...job, mode: 'generate' });
    try {
        await pc.init(job.canvasWidth || null);
        const dataUrl = await pc.exportDataUrl();
        const fd = new FormData();
        fd.append('donation_id', job.donationId);
        fd.append('type', job.type);
        fd.append('template_id', job.templateId);
        fd.append('layout_snapshot', JSON.stringify(pc.serialize(true)));
        fd.append('image', dataUrlToBlob(dataUrl), 'poster.png');

        const res = await fetch(job.uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': job.csrf || csrfToken(), Accept: 'application/json' },
            body: fd,
        });
        if (!res.ok) {
            throw new Error('Sunucu hatası (' + res.status + ')');
        }
        return await res.json();
    } finally {
        pc.dispose();
        holder.remove();
    }
}

async function handleGenerate(payload) {
    const jobs = Array.isArray(payload?.jobs) ? payload.jobs : (Array.isArray(payload) ? payload : []);
    if (!jobs.length) {
        return;
    }
    let ok = 0;
    for (const job of jobs) {
        try {
            // eslint-disable-next-line no-await-in-loop
            await runGenerateJob(job);
            ok += 1;
        } catch (e) {
            // eslint-disable-next-line no-console
            console.error('Afiş üretilemedi:', e);
        }
    }
    if (window.Livewire) {
        window.Livewire.dispatch('bkd-poster-saved', { ok });
    }
}

/* ---------------------------------------------------------------------------
 | Bootstrap
 | ------------------------------------------------------------------------- */

function bootEditors() {
    document.querySelectorAll('[data-poster-editor]').forEach(initEditor);
}

function registerLivewireListener() {
    if (window.__bkdPosterGenReg || !window.Livewire) {
        return;
    }
    window.__bkdPosterGenReg = true;
    window.Livewire.on('bkd-generate-poster', handleGenerate);
}

document.addEventListener('DOMContentLoaded', () => {
    bootEditors();
    registerLivewireListener();
});
document.addEventListener('livewire:init', registerLivewireListener);
document.addEventListener('livewire:navigated', () => {
    bootEditors();
    registerLivewireListener();
});

registerLivewireListener();
bootEditors();
