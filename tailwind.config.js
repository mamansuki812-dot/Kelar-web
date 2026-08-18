/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0E8388',
        'primary-dark': '#095B5F',
        secondary: '#FA8F20',
        'secondary-dark': '#D97207',
        success: '#10B981',
        'neutral-dark': '#0F172A',
        muted: '#64748B',
        surface: '#FFFFFF',
        'body-bg': '#F8FAFC',
        'border-soft': '#E2E8F0',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['"Plus Jakarta Sans"', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
