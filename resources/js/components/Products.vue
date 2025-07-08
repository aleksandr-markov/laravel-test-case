<template>
    <div class="flex gap-6 p-6">
        <!-- Filters -->
        <aside class="w-1/4 p-4 rounded">
            <div>
                <div v-for="activeFilter in activeFilters"
                    class="rounded-md bg-slate-800 py-0.5 px-2.5 border border-transparent text-sm text-white transition-all shadow-sm">
                    <span></span>
                </div>

                <div class="flex gap-4 sm:gap-6 flex-col">
                    <!-- <Filter :filters="filters" @update-filters="handleUpdateFilters" /> -->

                    <div v-for="filter in filters" :key="filter.slug" class="filter-group">
                        <h4>{{ filter.name }}</h4>
                        <div v-for="option in filter.values" :key="option">
                            <label>
                                <input type="checkbox" :value="option.value" v-model="activeFilters[filter.slug]" />
                                {{ option.value }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Products -->
        <main>
            <div v-if="products">
                <div class="flex-1 grid grid-cols-3 gap-4">
                    <Product v-for="product in products.data" :key="product.id" :product="product"> </Product>
                </div>
            </div>

            <div v-else>
                loading
            </div>

            <div>
                <Pagination :totalPages="products.last_page" :perPage="products.per_page" :currentPage="currentPage"
                    @pagechanged="onPageChange" />
            </div>
        </main>


    </div>
</template>

<script>
import axios from "axios";
import Filter from "./Filter.vue";
import Pagination from "./Pagination.vue";
import Product from "./Product.vue"

export default {
    components: {
        Filter,
        Pagination,
        Product,
    },
    data() {
        return {
            products: [],
            filters: [],
            activeFilters: {},
            currentPage: 1,
            limit: 100,
            sort_by: "",
        };
    },

    mounted() {
        this.getProducts();
        this.getFilters();
    },


    
    watch: {
        activeFilters: {
            handler() {
                this.getProducts();
            },
            deep: true,
            immediate: true,
        },
        currentPage: {
            handler() {
                this.getProducts();
            }
        }
    },

    methods: {
        onPageChange(page) {
            console.log(page)
            this.currentPage = page;
        },
        getProducts() {
            const params = {
                page: this.currentPage,
                limit: this.limit,
                sort_by: this.sort_by,
                ...this.serializeFilters(),
            };

            axios
                .get("/api/catalog/products", { params })
                .then((response) => {
                    console.log(response.data);
                    this.products = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        getFilters() {
            const params = { ...this.serializeFilters() };
            axios
                .get("/api/catalog/filters", { params })
                .then((response) => {
                    this.filters = response.data;

                    this.activeFilters = Object.fromEntries(
                        this.filters.map(f => [f.slug, []])
                    );
                    
                })
                .catch((error) => {
                    console.error("Error fetching filters:", error);
                });
        },
        serializeFilters() {
            const params = {};

            Object.entries(this.activeFilters).forEach(([key, values]) => {
                values.forEach((value, i) => {
                    params[`filter[${key}][${i}]`] = value;
                });
            });

            return params;
        },

        handleUpdateFilters({ param, values }) {
            console.log(param, values)
        },
    },
};
</script>
