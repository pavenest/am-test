<template>

	<div class="sec-header">
		<el-button type="primary">Add event</el-button>
		<el-button type="primary">Recurring event</el-button>
		<el-button type="primary">Refresh list</el-button>
	</div>

	<div style="border: dashed grey 1px; padding: 10px">

		date - name - venue - cost - settings

	</div>

</template>

<script>
export default {
	name: "Events"
}
</script>

<style scoped>

.sec-header {
	border: dashed 1px red;
	padding: 5px;
	margin-bottom: 5px;
}
</style>