import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));

export default {
  plugins: {
    "@tailwindcss/postcss": {},
    "autoprefixer": {},
    "cssnano": {},
    [resolve(__dirname, 'postcss-unwrap-layer.js')]: {}
  },
};
