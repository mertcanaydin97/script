<template>
	<a-menu
	
		style="width: 100%"
		mode="inline"
	>
		<template v-for="category in allCategories" :key="category.xid">


			<a-menu-item :key="category.xid" v-if="category.children">
			<router-link class="parent"
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
			<router-link  class="sinlge"
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
	</a-menu>
</template>
<style>a.child {
    padding-left: 16px;
}
.child .child {
    padding-left: 24px;
}
.child .child .child {
    padding-left: 32px;
}</style>
<script>
import { defineComponent, onMounted, ref, watch } from "vue";
import {
	MailOutlined,
	CalendarOutlined,
	AppstoreOutlined,
	SettingOutlined,
} from "@ant-design/icons-vue";
import { sortBy } from "lodash-es";
import CategoryMenu from "./CategroyMenu.vue";
import common from "../../../../common/composable/common";

export default defineComponent({
	props: ["catSelectedKeys", "catOpenKeys"],
	components: {
		MailOutlined,
		CalendarOutlined,
		AppstoreOutlined,
		SettingOutlined,
		CategoryMenu,
	},
	setup(props) {
		const allCategories = ref([]);
		const selectedKeys = ref([]);
		const openKeys = ref([]);

		const { frontWarehouse } = common();
		onMounted(() => {
			selectedKeys.value = props.catSelectedKeys;
			getCategories();
		});

		const getCategories = () => {
			axiosFront.post("front/categories").then((response) => {
				const allCategoriesArray = [];
				var listArray = response.data.categories;
				// listArray = sortBy(listArray, "x_parent_id");

				listArray.forEach((node) => {
					// No parentId means top level
					if (!node.x_parent_id) return allCategoriesArray.push(node);

					// Insert node as child of parent in listArray array
					const parentIndex = listArray.findIndex(
						(el) => el.xid === node.x_parent_id
					);
					if (!listArray[parentIndex].children) {
						return (listArray[parentIndex].children = [node]);
					}

					listArray[parentIndex].children.push(node);
				});

				allCategories.value = allCategoriesArray;
				
			});
		};

		const getPath = (model, id) => {
			var path,
				item = model.id;

			if (!model || typeof model !== "object") return;

			if (model.id === id) return [item];

			(model.children || []).some((child) => (path = getPath(child, id)));
			return path && [item].concat([...path]);
		};

		watch(props, (newVal, oldVal) => {
			selectedKeys.value = newVal.catSelectedKeys;

			let parentIds = [];
			allCategories.value.forEach((nodeItem) => {
				const result = getPath(nodeItem, selectedKeys.value[0]);
				if (result != undefined && result.includes(selectedKeys.value[0])) {
					parentIds = result;
				}
			});

			openKeys.value = parentIds;
		});

		return {
			selectedKeys,
			openKeys,
			allCategories,
			frontWarehouse
		};
	},
});
</script>
