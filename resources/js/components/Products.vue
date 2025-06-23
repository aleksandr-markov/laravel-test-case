<template>
    <div class="flex gap-6 p-6">
        <!-- Filters -->
        <aside class="w-1/4 p-4 rounded">
            <div>
                <div class="flex gap-4 sm:gap-6 flex-col">
                    <Filter :filters="filters" />
                </div>
            </div>
        </aside>

        <!-- Products -->
        <main class="flex-1 grid grid-cols-3 gap-4">
            <div
                v-for="product in products.data"
                :key="product.id"
                class="border border-black p-4 rounded-3xl"
            >
                {{ product.name }}
            </div>
        </main>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            products: [],
            filters: [],
            activeFilters: {},
            currentPage: 1,
            limit: 10,
            sort_by: "",
            filter: "",
        };
    },

    mounted() {
        console.log("Component mounted.");

        this.getProducts();
        this.getFilters();
    },

    watch: {
        activeFilters: {
            handler() {
                this.getProducts();
                this.getFilters();
            },
            deep: true,
            immediate: true,
        },
    },

    methods: {
        getProducts() {
            axios
                .get("/api/catalog/products", {
                    params: {
                        page: this.currentPage,
                        limit: this.limit,
                        sort_by: this.sort_by,
                        filter: this.filter,
                    },
                })
                .then((response) => {
                    console.log(response.data);
                    this.products = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        getFilters() {
            axios
                .get("/api/catalog/filters", {
                    params: Object.entries(this.activeFilters).reduce(
                        (acc, [key, values]) => {
                            acc[`filter[${key}]`] =
                                values.length === 1 ? values[0] : values;
                            return acc;
                        },
                        {}
                    ),
                })
                .then((response) => {
                    this.filters = response.data;
                })
                .catch((error) => {
                    console.error("Error fetching filters:", error);
                });
        },
        toggleFilter(param, value) {
            if (!this.activeFilters[param]) {
                this.activeFilters[param] = [];
            }

            const index = this.activeFilters[param].indexOf(value);

            if (index === -1) {
                this.activeFilters[param].push(value);
            } else {
                this.activeFilters[param].splice(index, 1);
            }
        },
        isActive(param, value) {
            return (this.activeFilters[param] || []).includes(value);
        },
    },
};
</script>
