<template>
	<a-card
		class="page-content-sub-header"
		:bodyStyle="{ padding: '0px', margin: '0px 16px 0' }"
	>
		<a-row>
			<a-col :span="24">
				<a-page-header :title="$t(`menu.online_orders`)" class="p-0" >
				<template #extra>
					<ExprotTable
						:exportType="'all-orders'"
						:tableName="'all-orders'"
						:title="$t('menu.users_reports')"
					/>
				</template>
				</a-page-header>
			</a-col>
			<a-col :span="24">
				<a-breadcrumb separator="-" style="font-size: 12px">
					<a-breadcrumb-item>
						<router-link :to="{ name: 'admin.dashboard.index' }">
							{{ $t(`menu.dashboard`) }}
						</router-link>
					</a-breadcrumb-item>
					<a-breadcrumb-item>
						{{ $t(`menu.stock_management`) }}
					</a-breadcrumb-item>
					<a-breadcrumb-item>
						{{ $t(`menu.online_orders`) }}
					</a-breadcrumb-item>
				</a-breadcrumb>
			</a-col>
		</a-row>
	</a-card>

	<a-card class="page-content-container">
		<a-row :gutter="[8, 8]" class="mt-20 mb-20">
			<a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="4">
				<a-input-search
					style="width: 100%"
					v-model:value="filters.searchString"
					show-search
					:placeholder="
						$t('common.placeholder_search_text', [$t('stock.invoice_number')])
					"
				/>
			</a-col>
			<a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="4">
				<a-select
					v-model:value="filters.user_id"
					:placeholder="
						$t('common.select_default_text', [
							$t(`${orderPageObject.langKey}.user`),
						])
					"
					:allowClear="true"
					style="width: 100%"
					optionFilterProp="title"
					show-search
				>
					<a-select-option
						v-for="user in users"
						:key="user.id"
						:title="user.name"
						:value="user.xid"
					>
						{{ user.name }}
					</a-select-option>
				</a-select>
			</a-col>
			<a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="4">
				<a-select
					v-model:value="filters.product_id"
					placeholder="Product"
					:allowClear="true"
					style="width: 100%"
					optionFilterProp="title"
					show-search
				>
					<a-select-option
						v-for="product in products"
						:key="product.xid"
						:title="product.name"
						:value="product.xid"
					>
						{{ product.name }}
					</a-select-option>
				</a-select>
			</a-col>
			<a-col :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
				<DateRangePicker
					ref="serachDateRangePicker"
					@dateTimeChanged="
						(changedDateTime) => (filters.dates = changedDateTime)
					"
				/>
			</a-col>
		</a-row>

		<a-row>
			<a-col :span="24">
				<a-tabs v-model:activeKey="filters.order_status">
					<a-tab-pane
						key="all"
						:tab="`Tüm Siparişler`"
					/>


				</a-tabs>
			</a-col>
		</a-row>

		<ReportTable :orderType="'all-orders'" :filters="filters" />
	</a-card>
</template>

<script>
import { onMounted, watch, ref } from "vue";
import { PlusOutlined } from "@ant-design/icons-vue";
import { useRouter } from "vue-router";
import common from "../../../../common/composable/common";
import ReportTable from "../../../components/order/ReportTable.vue";
import DateRangePicker from "../../../../common/components/common/calendar/DateRangePicker.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import InBetaMode from "./InBetaMode.vue";

import ExprotTable from "../../../components/report-exports/ExportTable.vue";
export default {
	components: {
		PlusOutlined,
		ReportTable,
		DateRangePicker,
		AdminPageHeader,
		InBetaMode,
		ExprotTable,
	},
	setup() {
		const {
			formatAmountCurrency,
			orderType = 'all-orders',
			orderPageObject,
			orderStatus,
			permsArray,
			selectedWarehouse,
		} = common();
		const router = useRouter();
		const storeUrl = ref("");

		const users = ref([]);
		const products = ref([]);
		const serachDateRangePicker = ref(null);

		const filters = ref({
			payment_status: "all",
			user_id: undefined,
			product_id: undefined,
			dates: [],
			searchColumn: "invoice_number",
			searchString: "",
		});

		onMounted(() => {
			generateStorePath();
			const usersPromise = axiosAdmin.get(orderPageObject.value.userType);

			const productsPromise = axiosAdmin.get("products?fields=id,xid,name&limit=10000");
			Promise.all([productsPromise]).then(([productsResponse]) => {
				products.value = productsResponse.data;
			});
			Promise.all([usersPromise]).then(([usersResponse]) => {
				users.value = usersResponse.data;
			});
		});

		const generateStorePath = () => {
			const storePathString = router.resolve({
				name: "front.homepage",
				params: { warehouse: selectedWarehouse.value.slug },
			});
			storeUrl.value = window.config.path + storePathString.href;
		};

		watch(selectedWarehouse, (newVal, oldVal) => {
			generateStorePath();

			filters.value = {
				order_status: "all",
				user_id: undefined,
				product_id: undefined,
				dates: [],
				searchColumn: "invoice_number",
				searchString: "",
			};

			serachDateRangePicker.value.resetPicker();
		});

		return {
			orderPageObject,

			permsArray,
			orderStatus,
			formatAmountCurrency,

			users,
			products,

			filters,
			orderType,
			serachDateRangePicker,

			selectedWarehouse,
			storeUrl,
		};
	},
};
</script>
