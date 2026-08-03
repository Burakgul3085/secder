/** @type {import('tailwindcss').Config} */

/**
 * Kurumsal renk paleti — Secder logosundan türetilmiştir.
 * Ana ton, logodaki dingin lacivert-mavi (#5f6f9b) üzerine kuruludur.
 * Not: Mevcut şablonlarda yüzlerce `cyan/teal/brand` sınıfı kullanıldığı için
 * palet tek noktadan bu skalaya eşlenir; böylece işaretleme (HTML) hiç
 * değişmeden tüm site yeni kurumsal kimliğe geçer.
 */
const secder = {
  50: '#f5f7fb',
  100: '#e8edf6',
  200: '#d1dbec',
  300: '#aebfda',
  400: '#8397bd',
  500: '#5f6f9b', // logo tonu
  600: '#4d5c83', // birincil (buton, vurgu)
  700: '#3f4c6b', // birincil metin tonu
  800: '#333c55',
  900: '#2b3245',
  950: '#1a1f2c',
};

export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/Filament/**/*.php',
    './app/Support/HeroImageSpec.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
        serif: ['Lora', 'Georgia', 'ui-serif', 'serif'],
      },
      colors: {
        secder,
        brand: secder,
        // Eski temadan gelen sınıf adları korunur, renkleri kurumsal palete bağlanır.
        cyan: secder,
        teal: secder,
      },
    },
  },
  plugins: [],
};
