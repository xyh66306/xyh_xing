<template>
	<view class="page">
		<view class="form">
			<u--form labelPosition="top" ref="pwdForm" labelWidth="auto">
				<u-form-item label="邮箱" prop="email" borderBottom ref="email">
					<u--input v-model="email" border="none" placeholder="请输入邮箱"></u--input>
				</u-form-item>
				<u-form-item label="验证码" prop="code" borderBottom ref="code">
					<u-input type="number" v-model="code" border="none" placeholder="请输入验证码">
						<template slot="suffix">
							<u-code ref="uCode" @change="codeChange"></u-code>
							<u-button @tap="getCode" :text="tips" type="primary" size="mini"></u-button>
						</template>
					</u-input>
				</u-form-item>
				<u-form-item label="密码" prop="paypwd" borderBottom ref="paypwd">
					<u--input type="password" v-model="paypwd" border="none" placeholder="支付密码为6位纯数字"
						maxlength="6"></u--input>
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
				email: '',
				code: '',
				paypwd: '',
				tips: '',
			}
		},
		methods: {
			getCode() {
				if (this.$refs.uCode.canGetCode) {
					// 模拟向后端请求验证码
					uni.showLoading({
						title: '正在获取验证码'
					})
					uni.$u.http.post('/api/ems/send', {
						event:'resetpwd',
						email: this.email
					}).then(res => {
						uni.hideLoading();
						if (res.code == 1) {
							// 这里此提示会被this.start()方法中的提示覆盖
							uni.$u.toast('验证码已发送');
							// 通知验证码组件内部开始倒计时
							this.$refs.uCode.start();
						} else {
							uni.$u.toast(res.msg);
						}
			
					}).catch(res => {
						console.log(res)
						uni.hideLoading();
					});
				} else {
					uni.$u.toast('倒计时结束后再发送');
				}
			},
			codeChange(text) {
				this.tips = text;
			},			
			changepwd() {
				// this.$refs.pwdForm.validate().then(res => {
				// 	uni.$u.toast('修改成功');
				// 	uni.navigateBack();
				// });
				uni.$u.http.post('/api/user/updatepaypwd', {
					paypwd:this.paypwd,
					email: this.email,
					captcha:this.code
				}).then(res => {
					uni.hideLoading();
					if (res.code == 1) {
						uni.$u.toast('修改成功');
						uni.navigateBack();
					} else {
						uni.$u.toast(res.msg);
					}
							
				}).catch(res => {
					console.log(res)
					uni.hideLoading();
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