<template>
	<a-menu
	
		style="width: 100%"
		mode="inline"
		class="catmenus"
	>

			<template v-for="category in allCategories" :key="category.xid">


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
}ul.ant-menu.ant-menu-root.ant-menu-inline.ant-menu-light.catmenus.categories-page-lefbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: row;
    flex-wrap: wrap;
    align-content: center;
}

ul.ant-menu.ant-menu-root.ant-menu-inline.ant-menu-light.catmenus.categories-page-lefbar li.ant-menu-item.ant-menu-item-only-child {
    width: auto;
    padding: 0px 10px;
    border-radius: 10px;
    border: 1px solid rgb(238, 238, 238);
}

ul.ant-menu.ant-menu-root.ant-menu-inline.ant-menu-light.catmenus.categories-page-lefbar span.ant-avatar.ant-avatar-circle {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    color: rgba(0, 0, 0, 0.85);
    font-size: 14px;
    font-variant: tabular-nums;
    line-height: 1.5715;
    list-style: none;
    font-feature-settings: 'tnum';
    position: relative;
    display: block;
    overflow: hidden;
    color: #fff;
    white-space: nowrap;
    text-align: center;
    vertical-align: middle;
    background: #ccc;
    width: 32px;
    height: 32px;
    line-height: 32px;
    border-radius: 50%;
    margin-right: 16px;
}

ul.ant-menu.ant-menu-root.ant-menu-inline.ant-menu-light.catmenus.categories-page-lefbar a.sinlge {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    justify-content: center;
    align-items: center;
}</style>
<script>
import { defineComponent, onMounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
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
		const route = useRoute();
		const allCategories = ref([]);
		const selectedKeys = ref([]);
		const openKeys = ref([]);

		const { frontWarehouse } = common();
		onMounted(() => {
			selectedKeys.value = props.catSelectedKeys;
			getCategories(route.params);
		});

		const getCategories = (params) => {
			var url = "front/categories";
			if (params && params.slug) {
				url = "front/categories/childs/"+params.slug;
			}
			axiosFront.post(url).then((response) => {
				const allCategoriesArray = [];
				var listArray = response.data.categories;
				
				allCategories.value = response.data.categories;
				
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
			/*allCategories.value.forEach((nodeItem) => {
				const result = getPath(nodeItem, selectedKeys.value[0]);
				if (result != undefined && result.includes(selectedKeys.value[0])) {
					parentIds = result;
				}
			});*/
		getCategories(route.params);
		console.log(newVal.catSelectedKeys)
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
