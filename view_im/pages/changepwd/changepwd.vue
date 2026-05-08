<template>
	<view class="page">
		<view class="form">
			<u--form labelPosition="top" :model="pwdForm" :rules="rules" ref="pwdForm" labelWidth="auto">
				<u-form-item label="旧密码" prop="oldpwd" borderBottom ref="oldpwd">
					<u--input type="password" v-model="pwdForm.oldpwd" border="none" placeholder="请输入旧密码"></u--input>
				</u-form-item>
				<u-form-item label="设置新密码" prop="newpwd" borderBottom ref="newpwd">
					<u--input type="password" v-model="pwdForm.newpwd" border="none" placeholder="请设置新密码"></u--input>
				</u-form-item>
				<u-form-item label="确认新密码" prop="newpwd2" borderBottom ref="newpwd2">
					<u--input type="password" v-model="pwdForm.newpwd2" border="none" placeholder="请确认新密码"></u--input>
				</u-form-item>
			</u--form>
		</view>
		<u-button type="primary" @click="changepwd" size="large" shape="circle">确定</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				pwdForm: {
					oldpwd: '',
					newpwd: '',
					newpwd2: ''
				},
				rules: {
					oldpwd: {
						type: 'string',
						required: true,
						message: '请输入旧密码',
						trigger: ['blur', 'change']
					},
					newpwd: {
						type: 'string',
						required: true,
						message: '请输入新密码',
						trigger: ['blur', 'change']
					},
					newpwd2: [{
						type: 'string',
						required: true,
						message: '请再次输入新密码',
						trigger: ['blur', 'change']
					}, {
						// 自定义验证函数，见上说明
						validator: (rule, value, callback) => {
							// 上面有说，返回true表示校验通过，返回false表示不通过
							// uni.$u.test.mobile()就是返回true或者false的
							return this.pwdForm.newpwd === this.pwdForm.newpwd2;
						},
						message: '新密码不一致',
						// 触发器可以同时用blur和change
						trigger: ['blur'],
					}, ]
				},
			}
		},
		methods: {
			changepwd() {
				this.$refs.pwdForm.validate().then(res => {
					uni.$u.toast('修改成功');
					uni.navigateBack();
				});
			}
		}
	}
</script>

<style lang="scss">
	.page {
		padding: 30rpx;
	}

	.form {
		margin-bottom: 60rpx;
	}
</style>