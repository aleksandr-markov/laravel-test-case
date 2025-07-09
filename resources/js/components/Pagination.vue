<template>
    <ul class="inline-flex -space-x-px text-sm">
        <li
            class="pagination-first-item ">
            <button type="button" @click="onClickFirstPage" :disabled="isInFirstPage">
                First
            </button>
        </li>

        <li class="pagination-center-item ">
            <button type="button" @click="onClickPreviousPage" :disabled="isInFirstPage">
                Previous
            </button>
        </li>

        <!-- Visible Buttons Start -->

        <li v-for="page in pages" :key="page.name" class="pagination-center-item " :class="{ 'pagination-active-item': isPageActive(page.name) }">
            <button type="button" @click="onClickPage(page.name)" :disabled="page.isDisabled">
                {{ page.name }}
            </button>
        </li>

        <!-- Visible Buttons End -->

        <li class="pagination-center-item">
            <button type="button" @click="onClickNextPage" :disabled="isInLastPage">
                Next
            </button>
        </li>

        <li class="pagination-last-item">
            <button type="button" @click="onClickLastPage" :disabled="isInLastPage">
                Last
            </button>
        </li>
    </ul>
</template>
<script>

export default {
    props: {
        maxVisibleButtons: {
            type: Number,
            required: false,
            default: 5,
        },
        totalPages: {
            type: Number,
            required: true,
        },
        perPage: {
            type: Number,
            required: true,
        },
        currentPage: {
            type: Number,
            required: true,
        }
    },
    computed: {
        startPage() {
            if (this.currentPage === 1) {
                return 1;
            }

            if (this.currentPage === this.totalPages) {
                return this.totalPages - this.maxVisibleButtons;
            }

            return this.currentPage - 1;
        },

        pages() {
            const range = [];

            for (let i = this.startPage; i <= Math.min(this.startPage + this.maxVisibleButtons - 1, this.totalPages); i++) {
                range.push({
                    name: i,
                    isDisabled: i === this.currentPage
                })
            }

            return range;
        },
        isInFirstPage() {
            return this.currentPage === 1;
        },
        isInLastPage() {
            return this.currentPage === this.totalPages;
        },
    },
    methods: {
        onClickFirstPage() {
            this.$emit('pagechanged', 1);
        },
        onClickPreviousPage() {
            this.$emit('pagechanged', this.currentPage - 1);
        },
        onClickPage(page) {
            this.$emit('pagechanged', page);
        },
        onClickNextPage() {
            this.$emit('pagechanged', this.currentPage + 1);
        },
        onClickLastPage() {
            this.$emit('pagechanged', this.totalPages);
        },
        isPageActive(page) {
            return this.currentPage === page;
        }
    }
}

</script>
