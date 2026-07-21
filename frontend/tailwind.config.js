/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          violet: '#7b2fff',
          'violet-dark': '#5a1fd4',
          'violet-light': '#9d5fff',
          cyan: '#00d4ff',
          'cyan-dark': '#00a8cc',
          'cyan-light': '#33ddff',
        },
        dark: {
          900: '#050508',
          800: '#0a0a12',
          700: '#12121c',
          600: '#1a1a28',
          500: '#252536',
        },
        surface: {
          dark: 'rgba(10, 10, 18, 0.85)',
          'dark-hover': 'rgba(18, 18, 28, 0.9)',
          light: 'rgba(255, 255, 255, 0.9)',
          'light-hover': 'rgba(255, 255, 255, 0.98)',
        },
      },
      fontFamily: {
        display: ['"Outfit"', '"Google Sans"', 'sans-serif'],
        code: ['"Fira Code"', 'monospace'],
        sans: ['"Google Sans"', 'sans-serif'],
      },
      letterSpacing: {
        tighter: '-0.04em',
        tightest: '-0.06em',
      },
      boxShadow: {
        glow: '0 0 24px rgba(123, 47, 255, 0.12)',
        'glow-cyan': '0 0 24px rgba(0, 212, 255, 0.12)',
        'glow-lg': '0 0 48px rgba(123, 47, 255, 0.16)',
        glass: '0 8px 32px rgba(0, 0, 0, 0.06)',
        'glass-dark': '0 8px 32px rgba(0, 0, 0, 0.35)',
        elev: '0 1px 0 rgba(255,255,255,0.04), 0 20px 50px rgba(0,0,0,0.25)',
      },
      transitionTimingFunction: {
        premium: 'cubic-bezier(0.22, 1, 0.36, 1)',
        expo: 'cubic-bezier(0.16, 1, 0.3, 1)',
      },
      animation: {
        float: 'float 8s ease-in-out infinite',
        'fade-in': 'fade-in 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards',
        'slide-up': 'slide-up 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards',
        'slide-down': 'slide-down 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards',
        'spin-slow': 'spin 12s linear infinite',
        'reveal-up': 'reveal-up 1s cubic-bezier(0.16, 1, 0.3, 1) forwards',
        'line-grow': 'line-grow 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-14px)' },
        },
        'fade-in': {
          from: { opacity: '0' },
          to: { opacity: '1' },
        },
        'slide-up': {
          from: { opacity: '0', transform: 'translateY(28px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        'slide-down': {
          from: { opacity: '0', transform: 'translateY(-8px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        'reveal-up': {
          from: { opacity: '0', transform: 'translateY(40px)', filter: 'blur(6px)' },
          to: { opacity: '1', transform: 'translateY(0)', filter: 'blur(0)' },
        },
        'line-grow': {
          from: { transform: 'scaleX(0)' },
          to: { transform: 'scaleX(1)' },
        },
      },
      backgroundImage: {
        'gradient-brand': 'linear-gradient(135deg, #7b2fff, #00d4ff)',
        'gradient-dark': 'linear-gradient(180deg, #050508 0%, #0a0a12 50%, #050508 100%)',
        'gradient-mesh':
          'radial-gradient(ellipse 80% 50% at 20% -10%, rgba(123,47,255,0.18), transparent 50%), radial-gradient(ellipse 60% 40% at 90% 10%, rgba(0,212,255,0.1), transparent 45%)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}
