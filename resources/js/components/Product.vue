<template>
    <div class="border border-black p-4 rounded-3xl">
        <a href="/">
            <div>
                <img v-if="product.pictures && product.pictures.length > 0 && product.pictures[0].img_src"
                    :src="product.pictures[0].img_src" alt="Product Image" />
            </div>

            <div>
                <strong>{{ product.name }}</strong>
            </div>

             <div>
                <strong>{{ product.price }} UAH</strong>
            </div>
        </a>
    </div>
</template>

<script>


export default {
    props: {
        product: {
            type: Object
        }
    }
}
</script>
