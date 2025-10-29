/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    // Thêm các đường dẫn mà Tailwind cần quét
    './resources/**/*.blade.php',
    './resources/**/*.js',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
