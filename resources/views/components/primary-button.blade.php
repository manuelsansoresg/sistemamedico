<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-bold text-white hover:bg-[#0051CC] focus:bg-[#0051CC] active:bg-[#0041a3] focus:outline-none focus:ring-2 focus:ring-[#0061F5] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
