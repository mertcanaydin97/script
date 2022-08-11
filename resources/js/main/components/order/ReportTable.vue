<template>
	<a-row>
		<a-col :span="24">
			<div class="table-responsive">
				<a-table
					:columns="columns"
					:row-key="(record) => record.xid"
					:data-source="table.data"
					:pagination="true"
					:loading="table.loading"
					@change="handleTableChange"
					:id="orderType"
				>
					<template #bodyCell="{ column, record }">
						<template v-if="column.dataIndex === 'order_date'">
							{{ formatDate(record.order_date) }}
						</template>
						<template v-if="column.dataIndex === 'product_name'">
							<span v-if="record.product_name">
								{{ record.product_name }}
							</span>
						</template>
						
						<template v-if="column.dataIndex === 'quantity'">
							<span v-if="record.quantity">
								{{ record.quantity }}
							</span>
						</template>
					</template>
					
				</a-table>
			</div>
		</a-col>
	</a-row>


</template>

<script>
import { onMounted, watch, ref, createVNode } from "vue";
import {
	EyeOutlined,
	PlusOutlined,
	EditOutlined,
	DeleteOutlined,
	ExclamationCircleOutlined,
	MoreOutlined,
	DownloadOutlined,
	CheckOutlined,
	StopOutlined,
	SendOutlined,
	SisternodeOutlined,
} from "@ant-design/icons-vue";
import { Modal, notification } from "ant-design-vue";
import { useRoute } from "vue-router";
import { find } from "lodash-es";
import { useI18n } from "vue-i18n";
import fields from "../../views/stock-management/purchases/fields";
import common from "../../../common/composable/common";
import datatable from "../../../common/composable/datatable";
import PaymentStatus from "../../../common/components/order/PaymentStatus.vue";
import OrderStatus from "../../../common/components/order/OrderStatus.vue";
import Details from "../../views/stock-management/purchases/Details.vue";
import UserInfo from "../../../common/components/user/UserInfo.vue";
import OrderDetails from "./OrderDetails.vue";
import ConfirmOrder from "../../views/stock-management/online-orders/ConfirmOrder.vue";
import ViewOrder from "../../views/stock-management/online-orders/ViewOrder.vue";
 
export default {
	props: ["orderType", "filters", "extraFilters", "pagination", "perPageItems"],
	components: {
		EyeOutlined,
		PlusOutlined,
		EditOutlined,
		DeleteOutlined,
		MoreOutlined,
		DownloadOutlined,
		ExclamationCircleOutlined,
		SisternodeOutlined,
		CheckOutlined,
		StopOutlined,
		SendOutlined,
		Details,
		UserInfo,
		Details,
		PaymentStatus,
		OrderStatus,
		OrderDetails,

		ConfirmOrder,
		ViewOrder,
	},
	setup(props) {
		const {
			
			hashableColumns,
			setupTableColumns,
			filterableColumns,
			pageObject,
			orderType,
			orderStatus,
			orderItemDetailsColumns,
		} = fields();
		const columns = ref([
		{
			title: 'Fatura No',
			dataIndex: "invoice_number",
		},
		{
			title: 'Sipariş Tarihi',
			dataIndex: "order_date",
		},
		{
			title: 'Ürün Adı',
			dataIndex: "product_name",
		},
		{
			title: 'Adet',
			dataIndex: "quantity",
		},
		
	]);  
		const datatableVariables = datatable();
		const {
			formatAmountCurrency,
			invoiceBaseUrl,
			permsArray,
			calculateOrderFilterString,
			formatDate,
			selectedWarehouse,
			selectedLang,
			orderStatusColors,
		} = common();
		const route = useRoute();
		const { t } = useI18n();
		const detailsDrawerVisible = ref(false);

		const selectedItem = ref({});

		// For Online Orders
		const confirmModalVisible = ref(false);
		const viewModalVisible = ref(false);
		const modalData = ref({});
		// End For Online Orders

		onMounted(() => {
			initialSetup();
		});

		const initialSetup = () => {
			orderType.value = props.orderType;
			if (props.perPageItems) {
				datatableVariables.table.pagination.pageSize = props.perPageItems;
			}
			datatableVariables.table.pagination.current = 1;
			datatableVariables.table.pagination.currentPage = 1;
			datatableVariables.hashable.value = hashableColumns;
			setupTableColumns();
			setUrlData();
		};

		const setUrlData = () => {
			const tableFilter = props.filters;

			const filterString = calculateOrderFilterString(tableFilter);

			var extraFilterObject = {};
			if (tableFilter.dates) {
				extraFilterObject.dates = tableFilter.dates;
			}
			if (tableFilter.transfer_type) {
				extraFilterObject.transfer_type = tableFilter.transfer_type;
			}

			datatableVariables.tableUrl.value = {
				url: `${props.orderType}?field=a`,
				filterString,
				filters: {
					user_id: tableFilter.user_id ? tableFilter.user_id : undefined,
					product_id: tableFilter.product_id ? tableFilter.product_id : undefined
				},
				extraFilters: extraFilterObject,
			};
			datatableVariables.table.filterableColumns = filterableColumns;

			if (
				tableFilter.searchColumn &&
				tableFilter.searchString &&
				tableFilter.searchString != ""
			) {
				datatableVariables.table.searchColumn = tableFilter.searchColumn;
				datatableVariables.table.searchString = tableFilter.searchString;
			} else {
				datatableVariables.table.searchColumn = undefined;
				datatableVariables.table.searchString = "";
			}

			datatableVariables.fetch({
				page: 1,
			});
		};

		const showDeleteConfirm = (id) => {
			Modal.confirm({
				title: t("common.delete") + "?",
				icon: createVNode(ExclamationCircleOutlined),
				content: t(`${pageObject.value.langKey}.delete_message`),
				centered: true,
				okText: t("common.yes"),
				okType: "danger",
				cancelText: t("common.no"),
				onOk() {
					axiosAdmin.delete(`${props.orderType}/${id}`).then(() => {
						setUrlData();
						notification.success({
							message: t("common.success"),
							description: t(`${pageObject.value.langKey}.deleted`),
						});
					});
				},
				onCancel() {},
			});
		};

		const viewItem = (record) => {
			selectedItem.value = record;
			detailsDrawerVisible.value = true;
		};

		const restSelectedItem = () => {
			selectedItem.value = {};
		};

		const paymentSuccess = () => {
			datatableVariables.fetch({
				page: datatableVariables.currentPage.value,
				success: (results) => {
					const searchResult = find(results, (result) => {
						return result.xid == selectedItem.value.xid;
					});

					if (searchResult != undefined) {
						selectedItem.value = searchResult;
					}
				},
			});
		};

		const onDetailDrawerClose = () => {
			detailsDrawerVisible.value = false;
		};

		// For Online Orders
		const confirmOrder = (order) => {
			modalData.value = order;
			confirmModalVisible.value = true;
		};

		const viewOrder = (order) => {
			modalData.value = order;
			viewModalVisible.value = true;
		};

		const changeOrderStatus = (order) => {
			processRequest({
				url: `online-orders/change-status/${order.unique_id}`,
				data: { order_status: order.order_status },
				success: (res) => {
					// Toastr Notificaiton
					notification.success({
						placement: "bottomRight",
						message: t("common.success"),
						description: t("online_orders.order_status_changed"),
					});
				},
				error: (errorRules) => {},
			});
		};

		const convertToSale = (order) => {
			Modal.confirm({
				title: t("quotation.convert_to_sale") + "?",
				icon: createVNode(ExclamationCircleOutlined),
				content: t(`quotation.convert_message`),
				centered: true,
				okText: t("common.yes"),
				okType: "danger",
				cancelText: t("common.no"),
				onOk() {
					axiosAdmin
						.post(`quotations/convert-to-sale/${order.unique_id}`)
						.then(() => {
							datatableVariables.fetch();

							// Toastr Notificaiton
							notification.success({
								placement: "bottomRight",
								message: t("common.success"),
								description: t("quotation.quotation_converted_to_sales"),
							});
						});
				},
				onCancel() {},
			});
		};

		const changeOrderItemStatus = (id,value) => {
			console.log(id,value)
			
 				axiosAdmin
						.post(`online-orders/change-single-item/${id}/${value}`)
						.then(() => {
							initialSetup();
							notification.success({
								message: t("common.success"),
								description: t(`online_orders.order_status_changed`),
								placement: "bottomRight",
							});
						});

		};
		const cancelOrder = (order) => {
			Modal.confirm({
				title: t("online_orders.cancel_order") + "?",
				icon: createVNode(ExclamationCircleOutlined),
				content: t(`online_orders.cancel_message`),
				centered: true,
				okText: t("common.yes"),
				okType: "danger",
				cancelText: t("common.no"),
				onOk() {
					axiosAdmin
						.post(`online-orders/cancel/${order.unique_id}`)
						.then(() => {
							initialSetup();
							notification.success({
								message: t("common.success"),
								description: t(`online_orders.order_cancelled`),
								placement: "bottomRight",
							});
						});
				},
				onCancel() {},
			});
		};

		const confirmDelivery = (order) => {
			Modal.confirm({
				title: t("common.delivered") + "?",
				icon: createVNode(ExclamationCircleOutlined),
				content: t(`online_orders.deliver_message`),
				centered: true,
				okText: t("common.yes"),
				okType: "danger",
				cancelText: t("common.no"),
				onOk() {
					axiosAdmin
						.post(`online-orders/delivered/${order.unique_id}`)
						.then(() => {
							initialSetup();
							notification.success({
								message: t("common.success"),
								description: t(`online_orders.order_delivered`),
								placement: "bottomRight",
							});
						});
				},
				onCancel() {},
			});
		};
		// End For Online Orders

		watch(props, (newVal, oldVal) => {
			initialSetup();
			restSelectedItem();
		});

		watch(selectedWarehouse, (newVal, oldVal) => {
			setUrlData();
		});

		return {
			columns,
			...datatableVariables,
			filterableColumns,
			pageObject,

			formatDate,
			orderStatus,
			orderStatusColors,

			setUrlData,
			formatAmountCurrency,
			invoiceBaseUrl,
			permsArray,

			selectedItem,
			viewItem,
			restSelectedItem,
			paymentSuccess,

			showDeleteConfirm,

			detailsDrawerVisible,
			onDetailDrawerClose,
			orderItemDetailsColumns,
			selectedLang,
			initialSetup,

			convertToSale,

			// For Online Orders
			changeOrderItemStatus,
			confirmOrder,
			cancelOrder,
			viewOrder,
			confirmDelivery,
			changeOrderStatus,
			confirmModalVisible,
			viewModalVisible,
			modalData,
			// End For Online Orders
		};
	},
};
</script>
