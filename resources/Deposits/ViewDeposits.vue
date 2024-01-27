<template>

	<div>
		Header
	</div>

	<div style="border: dashed grey 1px; padding: 10px">

		<el-form-item label-width="100px" label-position="left" label="Depositor">
			<el-select v-model="currentDepositorId" filterable placeholder="Select" @change="handleDepositorChange">
				<el-option
					v-for="item in depositor"
					:key="item.id"
					:label="item.name"
					:value="item.id"
				/>
			</el-select>
		</el-form-item>

		<hr/>


		<el-row :gutter="15">
			<el-col :span="16">
				<div>
					<el-button type="primary" @click="fetchRecords(currentDepositorId)"> Reload records</el-button>
				</div>
				<hr>

				<el-skeleton :rows="5" v-if="isLoading"/>

				<el-table v-if="!isLoading" :data="records" style="width: 100%" height="250">
					<el-table-column fixed prop="date" label="Date" width="150"/>
					<el-table-column prop="amount" label="Amount" width="120"/>
					<el-table-column prop="state" label="Note" width="120">
						<template #default="scope">
							<el-tag
								:type="scope.row.amount < 0 ? 'warning' : 'success'"
								disable-transitions >
								{{ scope.row.amount < 0 ? 'Withdraw' : 'Deposit' }}
							</el-tag>
						</template>
					</el-table-column>
					<el-table-column prop="note" label="Note"/>

					<el-table-column fixed="right" label="Operations" width="150">
						<template #default="scope">
							<el-button type="danger" size="small" @click="handleDelete(scope.row.id)">Delete</el-button>
							<el-button type="primary" size="small" @click="handleEdit(scope.row.id)">Edit</el-button>
						</template>
					</el-table-column>
				</el-table>

			</el-col>

			<el-col :span="8">

				<div class="dep-details-add">Deposit for: <strong>{{ depositorName }}</strong></div>

				<el-form
					label-position="left"
					label-width="100px"
					v-if="currentDepositorId"
				>

					<el-form-item label="Date">
						<el-date-picker
							v-model="form.date1"
							type="date"
							placeholder="Pick a date"
						/>
					</el-form-item>

					<el-form-item label="Amount">
						<el-input-number v-model="form.amount" :min="-99999" :max="99999"/>
					</el-form-item>

					<el-form-item label="Note">
						<el-input
							v-model="form.note"
							autosize
							type="textarea"
							placeholder="Note..."
						/>
					</el-form-item>


					<el-form-item>
						<el-button type="primary" @click="onSubmit"> Add</el-button>
					</el-form-item>

				</el-form>


			</el-col>
		</el-row>

		<hr>

		<pre>{{ depositor }}</pre>
		<label> How ...</label>


	</div>

	<div>
		body
	</div>

</template>

<script>
import {reactive, ref} from "vue";
import {ElMessage, ElMessageBox} from 'element-plus'

export default {
	name: "ViewDeposits",
	setup(props, ctx) {

		const currentDepositorId = ref('');
		const depositorName = ref('');
		const records = ref([
			{
				date: '2023-08-15',
				amount: '100',
				note: '',
			},
			{
				date: '2023-08-15',
				amount: -12,
				note: 'left the group',
			}
		]);
		const isLoading = ref(false)

		const depositor = ref([
			{
				id: 1,
				name: 'Atiq'
			},
			{
				id: 2,
				name: 'Nitesh'
			}

		]);

		const form = reactive({
			name: '',
			date1: new Date(),
			amount: 100,
			note: '',
		})


		const handleDepositorChange = (val) => {

			for (const elm of depositor.value) {
				if (elm.id === val) {
					depositorName.value = elm.name;
					break;
				}
			}
		}

		const onSubmit = () => {

			if (form.amount < 1) {
				ElMessageBox.confirm(
					'Amount is below zero, please confirm this is a withdrawal. Continue?',
					'Warning',
					{
						confirmButtonText: 'Continue',
						cancelButtonText: 'Cancel',
						type: 'warning',
					}
				)
					.then(() => {
						// ElMessage({
						// 	type: 'success',
						// 	message: 'Delete completed',
						// })


					})
					.catch(() => {
						// ElMessage({
						// 	type: 'info',
						// 	message: 'Delete canceled',
						// })
					})
			}


		}

		const fetchRecords = (depositorId) => {

			isLoading.value = true;

		}

		const handleDelete = (idd) => {

		}

		const handleEdit = (idd) => {

		}

		return {
			depositor,
			currentDepositorId,
			form,
			onSubmit,
			handleDepositorChange,
			depositorName,
			fetchRecords,
			records,
			isLoading,
			handleDelete,
			handleEdit
		}
	}
}
</script>

<style scoped>

.el-col:last-child {
	border: dotted 2px darkred;
	padding: 10px 0 15px 5px;
}

.dep-details-add {
	padding: 5px 0;
	text-align: center;
	border-bottom: solid 1px grey;
	margin-bottom: 10px;
}
</style>