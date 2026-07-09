@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-gold focus:ring-gold rounded-xl shadow-sm text-charcoal py-2.5 px-4 bg-gray-50/50 focus:bg-white transition-colors duration-200']) }}>
