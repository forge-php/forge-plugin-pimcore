{
    "name": "{{ $name }}",
    "description": "Vue 3 + Vite",
    "version": "0.0.1",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "preview": "vite preview"
    },
    "dependencies": {
        "vue": "^3.0.0"@if(count($dependencies) > 0),@endif
        @foreach($dependencies as $dependency => $version)
        "{{ $dependency }}": "{{ $version ?? '*' }}"@if(!$loop->last),@endif
        @endforeach
    },
    "devDependencies": {
        "@vitejs/plugin-vue": "^5.0.5",
        "typescript": "^5.5.3",
        "vite": "^5.3.4",
        "vue-tsc": "^2.0.28",
        "vite-plugin-symfony": "^6.4"
    }
}
