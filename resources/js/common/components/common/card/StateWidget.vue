<template>
	<a-card :bordered="false">
		<CardWidget>
			<a-space>
				<CardWidgetIcon>
					<slot name="image"></slot>
				</CardWidgetIcon>
				<figcaption>
					<slot name="description"></slot>
				</figcaption>
			</a-space>
		</CardWidget>
	</a-card>
</template>

<script>
import { CardWidget, CardWidgetIcon } from "./style";

export default {
	components: {
		CardWidget,
		CardWidgetIcon,
	},
};
</script>

<style></style>
