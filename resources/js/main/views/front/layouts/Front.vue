<template>
	<a-layout v-if="frontWarehouse.online_store_enabled == 1">
		<a-layout-header :style="{ background: '#2874f0', padding: 0 }">
			<a-row type="flex" justify="center">
				<a-col :span="20">
					<a-row type="flex" justify="space-between">
						<a-col
							:xs="12"
							:sm="12"
							:md="innerWidth >= 768 ? 6 : 12"
							:lg="4"
							:xl="4"
							v-if="frontWarehouse && frontWarehouse.slug"
						>
							<LeftSidebar />

							<router-link
								:to="{
									name: 'front.homepage',
									params: { warehouse: frontWarehouse.slug },
								}"
							>
								<img
									:style="{
										width: innerWidth >= 768 ? '150px' : '110px',
									}"
									:src="frontWarehouse.dark_logo_url"
								/>
							</router-link>
						</a-col>
						<a-col v-if="innerWidth >= 768" :md="12" :lg="12" :xl="12">
						
						
	<a-auto-complete
    v-model:value="value"
    :options="searcresults"
    style="width: 100%;
    max-width: 400px;
    margin-left: 50%;"
    placeholder="input here"
    @select="onSelect"
    @search="onSearch"
  >
<template #option="item">
        <template v-if="item.options">
          <span>
            {{ item.value }}
            <a
              style="float: right"
              href="https://www.google.com/search?q=antd"
              target="_blank"
              rel="noopener noreferrer"
            >
              more
            </a>
          </span>
        </template>
        <template v-else-if="item.value === 'all'">
          <a
            href="https://www.google.com/search?q=ant-design-vue"
            target="_blank"
            rel="noopener noreferrer"
          >
            View all results
          </a>
        </template>
        <template v-else>
          <div style="display: flex; justify-content: space-between">
            
            <span>
				<a-button  @click="showModal(item.id)"> {{ item.value }}</a-button>
              {{ item.count }}
            </span>
          </div>
        </template>
      </template>
      <a-input-search placeholder="input here" size="large"></a-input-search>
    
  </a-auto-complete>
  
  
						</a-col>
						<a-col
							:xs="12"
							:sm="12"
							:md="innerWidth >= 768 ? 6 : 12"
							:lg="8"
							:xl="8"
						>
							<div :style="{ textAlign: 'right' }">
								<CheckoutDrawer @openLoginModal="openLoginModal" />
								<Login
									:modalVisible="loginModalVisible"
									@modalClosed="loginModalClosed"
								/>
							</div>
						</a-col>
					</a-row>
				</a-col>
			</a-row>
		</a-layout-header>
		<a-layout-content>
			<div>
				<div :style="{ background: '#fff' }" class="subheader">
					<a-row type="flex" justify="center">
						<a-col :span="20">
							<a-row>
								<div class="subheader-menu-lists">
									<a-space
										v-if="
											frontAppSetting &&
											frontAppSetting.pages_widget
										"
									>
										<!-- <a-dropdown
											class="subheader-menu"
											overlayClassName="top-dropdown-box"
										>
											<AppstoreOutlined
												:style="{
													fontSize: '24px',
													verticalAlign: 'middle',
												}"
											/>
											<template #overlay>
												<LeftSidebarMenu />
											</template>
										</a-dropdown> -->
										<a
											v-for="(
												item, index
											) in frontAppSetting.pages_widget"
											:key="index"
											class="subheader-menu ml-25"
											@click.prevent
										>
											<router-link :to="item.value">
												{{ item.title }}
											</router-link>
										</a>
									</a-space>
								</div>
							</a-row>
						</a-col>
					</a-row>
				</div>

				<router-view></router-view>
				<Footer />
			</div>
		</a-layout-content>
		<a-modal v-model:visible="visible" centered :footer="null" :title="null" :width="850" :confirm-loading="confirmLoading"
      @ok="handleOk()">
		<a-row :gutter="[16, 16]" >
			<a-col :span="12">
				<div class="product-details-image">
					<span>
						<img :src="modal.image_url" />
					</span>
				</div>
			</a-col>
			<a-col :span="12">
				<a-typography-title :level="3">{{
					modal.name
				}}</a-typography-title>
				<div>
					<a-tag v-if="modal.category_id" color="purple">
						{{ modal.category.name }}
					</a-tag>
					<a-tag v-if="modal.brand_id" color="cyan">
						{{ modal.brand.name }}
					</a-tag>
				</div>
				<p class="mt-10" v-if="modal.description">
					{{ modal.description }}
				</p>
				<a-typography-title class="mt-20" :level="3">
					{{ formatAmountCurrency(getSalesPriceWithTax(modal2)) }}
				</a-typography-title>
				<div class="mt-10 mb-20">
					<a-button-group v-if="modal.cart_quantity > 0">
						<a-button
							@click="
								modal.cart_quantity -= 1;
								addItem(modal);
							"
							size="large"
						>
							<minus-outlined />
						</a-button>
						<a-button size="large">
							{{ modal.cart_quantity }}
						</a-button>
						<a-button
							@click="
								modal.cart_quantity += 1;
								addItem(modal);
							"
							size="large"
						>
							<plus-outlined />
						</a-button>
					</a-button-group>
					<a-button
						v-else
						@click="
							modal.cart_quantity++;
							addItem(modal);
						"
						type="primary"
						size="large"
					>
						<ShoppingCartOutlined />
						{{ $t("front_setting.add_to_cart") }}
					</a-button>
				</div>
			</a-col>
		</a-row>
	</a-modal>
	</a-layout>
	
	<div v-else class="no-online-store-container">
		<a-result
			status="warning"
			:title="$t('warehouse.no_online_store_exists')"
		></a-result>
	</div>
</template>
<script>
import { defineComponent, ref, watch } from 'vue';
import { useStore } from "vuex";
import { DownOutlined, MenuOutlined, AppstoreOutlined } from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";
import { message } from "ant-design-vue";
import ProductCard from "../components/ProductCard.vue";
import Footer from "./Footer.vue";
import CheckoutDrawer from "../components/CheckoutDrawer.vue";
import Login from "../components/Login.vue";
import LeftSidebar from "./LeftSidebar.vue";
import LeftSidebarMenu from "./LeftSidebarMenu.vue";
import { UserOutlined,ShoppingCartOutlined, MinusOutlined, PlusOutlined } from "@ant-design/icons-vue";

import { filter, forEach } from "lodash-es";
export default defineComponent({
	components: {
		DownOutlined,
		MenuOutlined,
		AppstoreOutlined,
		ProductCard, UserOutlined,ShoppingCartOutlined, MinusOutlined, PlusOutlined,
		Footer,
		CheckoutDrawer,
		Login,
		LeftSidebar,
		LeftSidebarMenu,
	},
	setup() {
		
		const { formatAmountCurrency } = common();
    const confirmLoading = ref(false);
		const visible = ref(false);
		const modal = ref([]);
		const modal2 = ref([]);
		const showModal = (id) => {
			
				visible.value = true;
				axiosFront.post("search-products/"+id+"/single").then((response) => {
		modal.value = response.data.product;
		modal2.value = response.data.details;
	 
			});
		};
		const handleOk = () => {
			
			
      confirmLoading.value = true;
	  
	  
    };
	const getSalesPriceWithTax = function getSalesPriceWithTax(product) {
  if (product.sales_tax_type == 'exclusive' && product.tax_id != '' && product.tax) {
    var taxRate = product.tax.rate;
    var salesPrice = product.sales_price;
    var taxAmount = salesPrice * (taxRate / 100);
    var finalSalesPrice = salesPrice + taxAmount;
    return finalSalesPrice;
  } else {
    return product.sales_price;
  }
};
    const value = ref('');
    	const searcresults = ref([]);
		const onSearch = (searchText) => {
		//console.log(searchText);
		var serachval = ' ';
		if(searchText){
		serachval = searchText;
		}
			
			
			axiosFront.post("search-products/"+serachval+"/quick").then((response) => {
		searcresults.value = response.data;
			});
			
		};
		const onSelect = (value) => {
		console.log('onSelect', value);
		};
		 watch(searcresults, () => {
		console.log('value', value.value);
		
		});
		watch(modal, (newVal, oldVal) => {
		});
		const addItem = (product) => {
			const cartItems = store.state.front.cartItems;
			const updatedCartItems = filter(
				cartItems,
				(cartItem) => cartItem.xid != product.xid
			);

			if (product.cart_quantity > 0) {
				updatedCartItems.push({
					...product,
					cart_quantity: product.cart_quantity,
				});
			}
	console.table(product)
			store.commit("front/addCartItems", updatedCartItems);
			message.success(`Item updated in cart`);
		};


		const store = useStore();
		const { frontWarehouse, frontAppSetting } = common();
		const searchValue = ref("");
		const loginModalVisible = ref(false);

		const openLoginModal = () => {
			loginModalVisible.value = true;
		};

		const loginModalClosed = () => {
			loginModalVisible.value = false;
		};
		const searchProducts = (value) => {
			axiosFront.post("search-products/"+value+"/quick").then((response) => {
				const allCategoriesArray = [];
				searcresults.data = response.data
				//var listArray = response.data.categories;
				// listArray = sortBy(listArray, "x_parent_id");

				/*listArray.forEach((node) => {
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

				allCategories.value = allCategoriesArray;*/
				
			});
		};

		return {
			frontAppSetting,
			searchValue,
			openLoginModal,
			loginModalClosed,
			searchProducts,
			loginModalVisible,
			frontWarehouse,
			 searcresults,
			 modal,
			 modal2,
			  showModal,
			  getSalesPriceWithTax,
			formatAmountCurrency,
			getSalesPriceWithTax,
			visible,
			addItem,
			onSearch,
			handleOk,
			onSelect,
			innerWidth: window.innerWidth,
			value: ref(''),
			openKeys: ref([]),
			confirmLoading,
			selectedKeys: ref([]),
		};
	},
});
</script>
<style lang="less" scoped>
.ant-carousel :deep(.slick-arrow.custom-slick-arrow) {
	width: 25px;
	height: 25px;
	font-size: 25px;
	color: #fff;
	background-color: rgba(31, 45, 61, 0.11);
	opacity: 0.3;
	z-index: 1;
}
.ant-carousel :deep(.custom-slick-arrow:before) {
	display: none;
}
.ant-carousel :deep(.custom-slick-arrow:hover) {
	opacity: 0.5;
}

.subheader {
	border-bottom: 1px solid #e5e7eb;

	.subheader-menu-lists {
		padding-top: 15px;
		padding-bottom: 15px;
	}

	.subheader-menu {
		font-size: 16px;
		font-weight: 500;
		color: rgba(0, 0, 0, 0.85);
	}
}

.top-dropdown-box {
	.ant-dropdown-content {
		margin-top: 50px;
	}
}

.no-online-store-container {
	height: 100%;
	width: 100%;
	display: flex;
	position: fixed;
	align-items: center;
	justify-content: center;
	background: #f8f8ff;
}
.certain-category-search-dropdown .ant-select-dropdown-menu-item-group-title {
  color: #666;
  font-weight: bold;
}

.certain-category-search-dropdown .ant-select-dropdown-menu-item-group {
  border-bottom: 1px solid #f6f6f6;
}

.certain-category-search-dropdown .ant-select-dropdown-menu-item {
  padding-left: 16px;
}

.certain-category-search-dropdown .ant-select-dropdown-menu-item.show-all {
  text-align: center;
  cursor: default;
}

.certain-category-search-dropdown .ant-select-dropdown-menu {
  max-height: 300px;
}
</style>

<style>
.ant-input-group > .ant-input:first-child, .ant-input-group-addon:first-child {
    background: transparent;
    outline: unset !important;
    border: unset;
    border-bottom: 3px solid #fff;
    color: #fff;
}

.ant-input-group > .ant-input::placeholder {
    color: #fff;
}

.ant-input-group-addon {
    background: transparent !important;
}

.ant-input-search > .ant-input-group > .ant-input-group-addon:last-child .ant-input-search-button:not(.ant-btn-primary) {
    background: transparent !important;
    border: unset !important;
    border-bottom: 3px solid #fff !important;
    color: #fff !important;
}

span.ant-select-selection-placeholder {
    color: #fff;
    display: none;
}

.ant-select-auto-complete.ant-select .ant-input:focus, .ant-select-auto-complete.ant-select .ant-input:hover {
    box-shadow: unset!important;
    border-color: #fff;
}

.ant-select-item-option-content button.ant-btn {
    width: 100% !important;
    max-width: 100% !important;
    background: transparent !important;
    display: block;
    text-align: left;
    border: unset !important;
    padding: 0 !important;
}

.ant-select-item-option-content span {
    width: 100%;
}</style>