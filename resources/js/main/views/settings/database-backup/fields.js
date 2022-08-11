import { useI18n } from "vue-i18n";

const fields = () => {
    const url = "database-backups";
    const { t } = useI18n();

    const columns = [
        {
            title: t("database_backup.file"),
            dataIndex: "name",
        },
        {
            title: t("database_backup.file_size"),
            dataIndex: "size",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    return {
        url,
        columns,
    }
}

export default fields;
