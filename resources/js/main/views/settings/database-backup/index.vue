<template>
	<AdminPageHeader>
		<template #header>
			<a-page-header :title="$t(`menu.taxes`)" class="p-0">
				<template #extra>
					<a-space>
						<GenerateDbBackup @success="backupCreated" />
						<BackupCommandSetting />
					</a-space>
				</template>
			</a-page-header>
		</template>
		<template #breadcrumb>
			<a-breadcrumb separator="-" style="font-size: 12px">
				<a-breadcrumb-item>
					<router-link :to="{ name: 'admin.dashboard.index' }">
						{{ $t(`menu.dashboard`) }}
					</router-link>
				</a-breadcrumb-item>
				<a-breadcrumb-item>
					{{ $t(`menu.settings`) }}
				</a-breadcrumb-item>
				<a-breadcrumb-item>
					{{ $t(`menu.database_backup`) }}
				</a-breadcrumb-item>
			</a-breadcrumb>
		</template>
	</AdminPageHeader>

	<a-row>
		<a-col :xs="24" :sm="24" :md="24" :lg="4" :xl="4" class="bg-setting-sidebar">
			<SettingSidebar />
		</a-col>
		<a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
			<a-card class="page-content-container">
				<a-row class="mt-20">
					<a-col :span="24">
						<a-alert
							type="warning"
							showIcon
							:message="$t('database_backup.backup_locaion_is')"
						/>

						<div class="table-responsive mt-20">
							<a-table
								:columns="columns"
								:row-key="(record) => record.id"
								:data-source="results"
								:pagination="false"
							>
								<template #bodyCell="{ column, record }">
									<template v-if="column.dataIndex == 'action'">
										<a-space>
											<a-typography-link
												:href="record.url"
												target="_blank"
											>
												{{ $t("common.download") }}
											</a-typography-link>
											<a-button
												type="link"
												@click="deleteBackup(record.name)"
											>
												{{ $t("common.delete") }}
											</a-button>
										</a-space>
									</template>
								</template>
							</a-table>
						</div>
					</a-col>
				</a-row>
			</a-card>
		</a-col>
	</a-row>
</template>

<script>
import { onMounted, ref, createVNode } from "vue";
import {
	PlusOutlined,
	EditOutlined,
	DeleteOutlined,
	ExclamationCircleOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { Modal } from "ant-design-vue";
import common from "../../../../common/composable/common";
import fields from "./fields";
import SettingSidebar from "../SettingSidebar.vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import GenerateDbBackup from "./GenerateDbBackup.vue";
import BackupCommandSetting from "./BackupCommandSetting.vue";

export default {
	components: {
		PlusOutlined,
		EditOutlined,
		DeleteOutlined,
		ExclamationCircleOutlined,
		SettingSidebar,
		AdminPageHeader,
		GenerateDbBackup,
		BackupCommandSetting,
	},
	setup() {
		const { t } = useI18n();
		const { permsArray } = common();
		const { url, columns } = fields();
		const results = ref([]);

		onMounted(() => {
			axiosAdmin.post("database-backups").then((response) => {
				results.value = response.data.data;
			});
		});

		const backupCreated = (response) => {
			results.value = response.data;
		};

		const deleteBackup = (fileName) => {
			Modal.confirm({
				title: t("database_backup.delete_backup"),
				icon: createVNode(ExclamationCircleOutlined),
				content: t("database_backup.are_you_sure_delete_backup"),
				okText: t("common.yes"),
				okType: "danger",
				cancelText: t("common.no"),
				onOk() {
					axiosAdmin
						.post("delete-backup", { file_name: fileName })
						.then((response) => {
							results.value = response.data.data;
						});
				},
				onCancel() {},
			});
		};

		return {
			permsArray,
			columns,
			results,
			deleteBackup,
			backupCreated,
		};
	},
};
</script>
