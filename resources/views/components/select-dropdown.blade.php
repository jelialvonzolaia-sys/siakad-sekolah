@props([
    'name',
    'id' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'searchable' => null,
    'submitOnSelect' => false,
    'onchange' => null,
    'optionValue' => null,
    'optionLabel' => null,
    'triggerClass' => '',
])

@php
    $id = $id ?? $name;
    $selectedValue = old($name, $value ?? '');

    // Normalisasi opsi array / collection
    $normalizedOptions = [];
    foreach ($options as $key => $opt) {
        if (is_array($opt)) {
            $val = $opt[$optionValue ?? 'value'] ?? $opt['id'] ?? $key;
            $lbl = $opt[$optionLabel ?? 'label'] ?? $opt['nama'] ?? $opt['nama_kelas'] ?? $opt['nama_mapel'] ?? $opt['name'] ?? $val;
        } elseif (is_object($opt)) {
            $valProp = $optionValue ?? (isset($opt->value) ? 'value' : (isset($opt->id) ? 'id' : null));
            $lblProp = $optionLabel ?? (isset($opt->label) ? 'label' : (isset($opt->nama) ? 'nama' : (isset($opt->nama_kelas) ? 'nama_kelas' : (isset($opt->nama_mapel) ? 'nama_mapel' : (isset($opt->name) ? 'name' : null)))));
            $val = $valProp ? $opt->{$valProp} : $key;
            $lbl = $lblProp ? $opt->{$lblProp} : (string)$opt;
        } else {
            $val = $key;
            $lbl = $opt;
        }
        $normalizedOptions[] = [
            'value' => (string) $val,
            'label' => (string) $lbl,
        ];
    }

    // Tentukan label terpilih awal
    $selectedOption = collect($normalizedOptions)->first(function ($item) use ($selectedValue) {
        return (string) $item['value'] === (string) $selectedValue && (string) $selectedValue !== '';
    });

    if (!$selectedOption && (string) $selectedValue === '') {
        $selectedOption = collect($normalizedOptions)->first(function ($item) {
            return (string) $item['value'] === '';
        });
    }

    $initialLabel = $selectedOption ? $selectedOption['label'] : '';
    $isSearchable = $searchable !== null ? (bool)$searchable : (count($normalizedOptions) > 5);
@endphp

<div
    x-data="{
        open: false,
        search: '',
        selectedValue: @js((string) $selectedValue),
        selectedLabel: @js($initialLabel),
        placeholderText: @js($placeholder ?? '-- Pilih --'),
        options: @js($normalizedOptions),
        isSearchable: @js($isSearchable),
        get filteredOptions() {
            if (!this.search.trim()) {
                return this.options;
            }
            const q = this.search.toLowerCase();
            return this.options.filter(opt => opt.label.toLowerCase().includes(q));
        },
        select(opt) {
            this.selectedValue = opt.value;
            this.selectedLabel = opt.label;
            this.open = false;
            this.search = '';

            this.$nextTick(() => {
                const hiddenInput = this.$refs.hiddenInput;
                if (hiddenInput) {
                    hiddenInput.value = opt.value;
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    @if($onchange)
                        try {
                            (function() { {!! $onchange !!} }).call(hiddenInput);
                        } catch (err) {
                            console.error('Dropdown onchange error:', err);
                        }
                    @endif
                }
                const validationInput = this.$refs.validationInput;
                if (validationInput) {
                    validationInput.value = opt.value;
                    validationInput.setCustomValidity('');
                    validationInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                @if($submitOnSelect)
                    this.$el.closest('form')?.submit();
                @endif
            });
        },
        setByValue(val) {
            const strVal = val === null || val === undefined ? '' : String(val);
            const found = this.options.find(opt => String(opt.value) === strVal);
            this.selectedValue = strVal;
            this.selectedLabel = found ? found.label : '';
            const hiddenInput = this.$refs.hiddenInput;
            if (hiddenInput) {
                hiddenInput.value = strVal;
            }
            const validationInput = this.$refs.validationInput;
            if (validationInput) {
                validationInput.value = strVal;
                validationInput.setCustomValidity('');
            }
        },
        init() {
            this.$watch('open', value => {
                if (value && this.isSearchable && this.$refs.searchInput) {
                    this.$nextTick(() => this.$refs.searchInput.focus());
                }
            });
        }
    }"
    @set-select-value.window="if ($event.detail && $event.detail.id === @js($id)) { setByValue($event.detail.value); }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative w-full"
>
    <!-- Hidden input untuk kompatibilitas form POST / PUT standar Laravel -->
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $id }}"
        :value="selectedValue"
        x-ref="hiddenInput"
    />

    @if($required)
        <!-- Hidden input untuk HTML5 constraint validation browser -->
        <input
            type="text"
            tabindex="-1"
            aria-hidden="true"
            :value="selectedValue"
            x-ref="validationInput"
            required
            class="absolute inset-0 opacity-0 pointer-events-none w-full h-full"
            oninvalid="this.setCustomValidity('Silakan pilih salah satu opsi dari daftar')"
            oninput="this.setCustomValidity('')"
        />
    @endif

    <!-- Tombol Utama (Trigger Box) -->
    <button
        type="button"
        @click="open = !open"
        class="w-full flex items-center justify-between px-3.5 py-2.5 bg-white rounded-lg border border-[#D1D5DB] text-left transition-all duration-150 focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 {{ $triggerClass }}"
        :class="{ 'border-[#1E9E63] ring-2 ring-[#1E9E63]/15': open }"
        aria-haspopup="listbox"
        :aria-expanded="open"
    >
        <span
            x-text="selectedLabel || placeholderText"
            class="text-sm break-words {{ $initialLabel ? 'font-medium text-[#1F2937]' : 'text-[#9CA3AF]' }}"
            :class="selectedLabel ? 'font-medium text-[#1F2937]' : 'text-[#9CA3AF]'"
        >
            {{ $initialLabel ?: ($placeholder ?? '-- Pilih --') }}
        </span>
        <span
            class="material-symbols-outlined text-[20px] text-[#6B7280] transition-transform duration-200 flex-shrink-0 ml-2"
            :class="{ 'rotate-180 text-[#1E9E63]': open }"
        >
            expand_more
        </span>
    </button>

    <!-- Panel Menu Pilihan (Dropdown List) -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-98"
        class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-[#E5E7EB] rounded-lg shadow-lg overflow-hidden"
        role="listbox"
    >
        <div x-show="isSearchable" class="p-2 border-b border-[#E5E7EB] bg-[#F9FAFB]">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-[#9CA3AF]">search</span>
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="search"
                    @click.stop
                    placeholder="Cari..."
                    class="w-full pl-8 pr-7 py-1.5 text-xs rounded-md border border-[#D1D5DB] bg-white text-[#1F2937] placeholder-[#9CA3AF] focus:outline-none focus:border-[#1E9E63] focus:ring-1 focus:ring-[#1E9E63]/20"
                />
                <button
                    type="button"
                    x-show="search.length > 0"
                    @click.stop="search = ''"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-[#9CA3AF] hover:text-[#4B5563]"
                >
                    <span class="material-symbols-outlined text-[14px]">close</span>
                </button>
            </div>
        </div>

        <div class="max-h-60 overflow-y-auto p-1 divide-y divide-[#F3F4F6]">
            <template x-for="opt in filteredOptions" :key="opt.value">
                <button
                    type="button"
                    @click.stop="select(opt)"
                    class="w-full flex items-center justify-between px-3 py-2 text-left text-xs sm:text-sm rounded-md transition-colors cursor-pointer"
                    :class="selectedValue == opt.value ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#374151] hover:bg-[#F4F6F8] hover:text-[#1F2937] font-medium'"
                    role="option"
                    :aria-selected="selectedValue == opt.value"
                >
                    <span x-text="opt.label" class="break-words pr-2"></span>
                    <span x-show="selectedValue == opt.value" class="material-symbols-outlined text-[18px] text-[#1E9E63] flex-shrink-0">check</span>
                </button>
            </template>
            <div x-show="filteredOptions.length === 0" class="py-4 text-center text-xs text-[#9CA3AF]">
                Tidak ada opsi yang cocok
            </div>
        </div>
    </div>
</div>
