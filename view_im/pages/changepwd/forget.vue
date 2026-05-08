<template>
	<view class="page">
		<view class="form">
			<u--form labelPosition="top" :model="loginForm" :rules="rules" ref="loginForm" labelWidth="auto">
				<u-form-item label="邮箱" prop="email" borderBottom ref="email">
					<u--input v-model="loginForm.email" border="none" placeholder="请输入邮箱"></u--input>
				</u-form-item>
				<u-form-item label="验证码" prop="code" borderBottom ref="code">
					<u-input type="number" v-model="loginForm.captcha" border="none" placeholder="请输入验证码">
						<template slot="suffix">
							<u-code ref="uCode" @change="codeChange"></u-code>
							<u-button @tap="getCode" :text="tips" type="primary" size="mini"></u-button>
						</template>
					</u-input>
				</u-form-item>
				<u-form-item label="密码" prop="newpassword" borderBottom ref="newpassword">
					<u--input type="password" v-model="loginForm.newpassword" border="none" placeholder="密码为6位以上字母和数字"
						maxlength="16"></u--input>
				</u-form-item>			
				<!-- -->
			</u--form>
		</view>
		<u-button type="primary" @click="submit" shape="circle">立即提交</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				loginForm: {
					type:'email',
					email: '',
					captcha: '',
					newpassword: '',
				},
				rules: {
					email: [{
						required: true,
						message: '请输入邮箱',
						trigger: ['blur', 'change']
					}, {
						type: 'email',
						message: '邮箱格式错误',
						trigger: ['blur']
					}, ],
					captcha: [{
						required: true,
						message: '请输入验证码',
						trigger: ['blur', 'change']
					}],
					newpassword: [{
						required: true,
						message: '请输入密码',
						trigger: ['blur', 'change']
					}, {
						min: 6,
						message: '密码至少6位',
						trigger: ['blur']
					}, ],
				},
				tips: '',
			}
		},
		onLoad(e) {
			this.invite = e.invite || '';
			if (this.invite) {
				this.loginForm.invite = this.invite;
			}
		},
		methods: {
			submit() {
				this.$refs.loginForm.validate().then(res => {
					uni.$u.http.post('/api/user/resetpwd', this.loginForm).then(res => {
						if(res.code==1){
							uni.$u.toast('已修改');
							uni.redirectTo({
								url:"/pages/login/login"
							})	
						}else{
							uni.$u.toast(res.msg);
						}					
					}).catch(res => {
						uni.$u.toast(res.msg);
						console.log(res);
					});
				}).catch(res => {})
			},
			codeChange(text) {
				this.tips = text;
			},
			getCode() {
				if (this.$refs.uCode.canGetCode) {
					// 模拟向后端请求验证码
					uni.showLoading({
						title: '正在获取验证码'
					})
					uni.$u.http.post('/api/ems/send', {
						email: this.loginForm.email,
						event:'resetpwd'
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

		}
	}
</script>

<style lang="scss" scoped>
	.page {
		width: 100vw;
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: 30rpx 30rpx 100rpx;
	}

	.welcome {
		font-size: 24px;
		font-weight: bold;
		margin-bottom: 60rpx;
	}

	.form {
		margin-bottom: 80rpx;
		background-color: #fff;
		border-radius: 20rpx;
		padding:20rpx;
		overflow: hidden;
	}

	::v-deep .u-upload__button {
		background-color: #fff;
	}
</style>