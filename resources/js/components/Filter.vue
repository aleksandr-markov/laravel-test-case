<template>
    <details class="group relative" v-for="filter in filters">
        <summary
            class="flex items-center gap-2 border-b border-gray-300 pb-1 text-gray-700 transition-colors hover:border-gray-400 hover:text-gray-900 [&::-webkit-details-marker]:hidden"
        >
            <span class="text-sm font-medium">
                {{ filter.name }}
            </span>

            <span class="transition-transform group-open:-rotate-180">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="size-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                    />
                </svg>
            </span>
        </summary>

        <div
            class="z-1999 max-h-96 overflow-y-auto w-64 divide-y divide-gray-300 rounded border border-gray-300 bg-white shadow-sm group-open:absolute group-open:start-0 group-open:top-8"
        >
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-sm text-gray-700"> 0 Selected </span>

                <button
                    type="button"
                    class="text-sm text-gray-700 underline transition-colors hover:text-gray-900"
                >
                    Reset
                </button>
            </div>

            <fieldset class="p-3">
                <legend class="sr-only">Checkboxes</legend>

                <div class="flex flex-col items-start gap-3">
                    <label
                        v-for="filterValue in filter.values"
                        for="Option1"
                        class="inline-flex items-center gap-3"
                    >
                        <input
                            @change="
                                toggleFilter(filter.slug, filterValue.value)
                            "
                            :name="filter.slug"
                            :value="filterValue.value"
                            type="checkbox"
                            class="size-5 rounded border-gray-300 shadow-sm"
                            id="Option1"
                        />

                        <span class="text-sm font-medium text-gray-700">
                            {{ filterValue.value }} ({{ filterValue.count }})
                        </span>
                    </label>
                </div>
            </fieldset>
        </div>
    </details>
</template>
<script>
export default {
    props: {
        filters: {
            type: Array,
            required: true,
        },

        value: {
            type: Object,
            default: () => ({}),
        },
    },

    data() {
        return {
            activeFilters: { ...this.value }, 
        };
    },

    watch: {
        value: {
            handler(newVal) {
                this.activeFilters = { ...newVal };
            },
            deep: true,
        },
    },

    methods: {
        toggleFilter(param, value) {
            const current = this.activeFilters[param] || [];
            const index = current.indexOf(value);

            if (index === -1) {
                current.push(value);
            } else {
                current.splice(index, 1);
            }

            this.$set(this.activeFilters, param, [...current]);
            this.$emit("input", { ...this.activeFilters });
        },

        resetFilter(param) {
            this.$set(this.activeFilters, param, []);
            this.$emit("input", { ...this.activeFilters });
        },

        selectedCount(param) {
            return this.activeFilters[param]?.length || 0;
        },
    },
};
</script>
