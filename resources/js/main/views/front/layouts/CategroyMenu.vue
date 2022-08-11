<template>
	<template v-for="category in categories" :key="category.xid">
	
	
			<a-menu-item :key="category.xid" v-if="category.children">
			<router-link class="child parent"
				:to="{
					name: 'front.categories',
					params: { warehouse: frontWarehouse.slug, slug: [category.slug] },
				}"
			>
				<a-avatar :size="16" :src="category.image_url" />
				{{ category.name }}
			</router-link>
			
		</a-menu-item>
				<CategoryMenu v-if="category.children" :categories="category.children" />
			<a-menu-item :key="category.xid" v-if="!category.children">
			<router-link  class="child"
				:to="{
					name: 'front.categories',
					params: { warehouse: frontWarehouse.slug, slug: [category.slug] },
				}"
			>
				<a-avatar :size="16" :src="category.image_url" />
				{{ category.name }}
			</router-link>
		</a-menu-item>
		
		
		
	</template>
</template>

<script>
import { defineComponent, ref } from "vue";
import common from "../../../../common/composable/common";

export default defineComponent({
	name: "category-menu",
	props: ["categories"],
	setup() {
		const { frontWarehouse } = common();

		return {
			frontWarehouse,
		};
	},
});
</script>

<style></style>
