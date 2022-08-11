<template>
	<a-button type="primary" @click="openDrawer">
		<template #icon> <SettingOutlined /> </template>
		{{ $t("database_backup.settings") }}
	</a-button>

	<a-drawer
		:title="$t('database_backup.backup_command_setting')"
		:width="drawerWidth"
		:visible="visible"
		:body-style="{ paddingBottom: '80px' }"
		:footer-style="{ textAlign: 'right' }"
		:maskClosable="false"
		@close="onClose"
	>
		<a-form layout="vertical">
			<a-row :gutter="16">
				<a-typography-paragraph>
					<blockquote>
						<a-typography-text type="warning">
							{{ $t("database_backup.window_command_path") }}
						</a-typography-text>
					</blockquote>
					<blockquote>
						<a-typography-text type="warning">
							{{ $t("database_backup.laragon_command_path") }}
						</a-typography-text>
					</blockquote>
					<blockquote>
						<a-typography-text type="warning">
							{{ $t("database_backup.linux_command_path") }}
						</a-typography-text>
					</blockquote>
				</a-typography-paragraph>

				<a-col :xs="24" :sm="24" :md="24" :lg="24">
					<a-form-item
						:label="$t('database_backup.mysqldump_command_path')"
						name="mysqldump_command"
						:help="
							rules.mysqldump_command
								? rules.mysqldump_command.message
								: null
						"
						:validateStatus="rules.mysqldump_command ? 'error' : null"
						class="required"
					>
						<a-input
							v-model:value="mysqlDumpCommand"
							:placeholder="
								$t('common.placeholder_default_text', [
									$t('database_backup.mysqldump_command_path'),
								])
							"
						/>
					</a-form-item>
				</a-col>
			</a-row>
		</a-form>
		<template #footer>
			<a-button
				type="primary"
				@click="onSubmit"
				style="margin-right: 8px"
				:loading="loading"
			>
				<template #icon> <SaveOutlined /> </template>
				{{ $t("common.update") }}
			</a-button>
			<a-button @click="onClose">
				{{ $t("common.cancel") }}
			</a-button>
		</template>
	</a-drawer>
</template>

<script>
import { defineComponent, ref, watch } from "vue";
import {
	PlusOutlined,
	LoadingOutlined,
	SaveOutlined,
	SettingOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import { useStore } from "vuex";
import apiAdmin from "../../../../common/composable/apiAdmin";
import common from "../../../../common/composable/common";

export default defineComponent({
	components: {
		PlusOutlined,
		LoadingOutlined,
		SaveOutlined,
		SettingOutlined,
	},
	setup() {
		const { appSetting } = common();
		const { addEditRequestAdmin, loading, rules } = apiAdmin();
		const { t } = useI18n();
		const mysqlDumpCommand = ref(appSetting.value.mysqldump_command);
		const visible = ref(false);
		const store = useStore();

		const onSubmit = () => {
			const newFormData = {
				mysqldump_command: mysqlDumpCommand.value,
			};

			addEditRequestAdmin({
				url: "update-mysqldump-command",
				data: newFormData,
				successMessage: t("database_backup.command_updated"),
				success: (res) => {
					store.dispatch("auth/updateApp");
					visible.value = false;
				},
			});
		};

		const openDrawer = () => {
			rules.value = {};
			visible.value = true;
		};

		const onClose = () => {
			rules.value = {};
			visible.value = false;
		};

		watch(appSetting, (newVal, oldVal) => {
			mysqlDumpCommand.value = newVal.mysqldump_command;
		});

		return {
			visible,
			loading,
			rules,
			onClose,
			openDrawer,
			onSubmit,
			mysqlDumpCommand,

			drawerWidth: window.innerWidth <= 991 ? "90%" : "35%",
		};
	},
});
</script>
