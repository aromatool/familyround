import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
	plugins: [react()],
	build: {
		outDir: 'assets/dist',
		emptyOutDir: true,
		rollupOptions: {
			input: 'src/main.jsx',
			output: {
				entryFileNames: 'main.js',
				format: 'iife',
				assetFileNames: 'main[extname]',
			},
		},
	},
});
