<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex justify-center items-center px-4 py-3 bg-charcoal border border-transparent rounded-xl font-outfit font-semibold text-sm text-white tracking-wide hover:bg-black focus:bg-black active:bg-black focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 transition-all duration-200 shadow-md shadow-charcoal/10']) }}>
    {{ $slot }}
</button>
