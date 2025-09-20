<template>
	<view class="page">
		<view class="welcome">注册账户</view>
		<view class="form">
			<u--form labelPosition="top" :model="loginForm" :rules="rules" ref="loginForm" labelWidth="auto">
				<u-form-item label="用户名" prop="nickname" borderBottom ref="nickname">
					<u--input v-model="loginForm.nickname" border="none" placeholder="请输入用户名"></u--input>
				</u-form-item>
				<u-form-item label="手机号" prop="mobile" borderBottom ref="mobile">
					<u--input v-model="loginForm.mobile" border="none" placeholder="请输入手机号"></u--input>
				</u-form-item>
				<u-form-item label="邮箱" prop="email" borderBottom ref="email">
					<u--input v-model="loginForm.email" border="none" placeholder="请输入邮箱"></u--input>
				</u-form-item>
				<u-form-item label="验证码" prop="code" borderBottom ref="code">
					<u-input type="number" v-model="loginForm.code" border="none" placeholder="请输入验证码">
						<template slot="suffix">
							<u-code ref="uCode" @change="codeChange"></u-code>
							<u-button @tap="getCode" :text="tips" type="primary" size="mini"></u-button>
						</template>
					</u-input>
				</u-form-item>
				<u-form-item label="密码" prop="password" borderBottom ref="password">
					<u--input type="password" v-model="loginForm.password" border="none" placeholder="密码为6位以上字母和数字"
						maxlength="16"></u--input>
				</u-form-item>
				<u-form-item label="邀请码" prop="invite" borderBottom ref="invite">
					<u--input type="number" v-model="loginForm.invite" border="none" disabled placeholder="请输入邀请码"></u--input>
				</u-form-item>
				<!-- -->
			</u--form>
		</view>
		<u-button type="primary" @click="register" shape="circle">注册</u-button>
		<u-button type="primary" class="loginbtn" @click="gologin" shape="circle">登录</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				loginForm: {
					email: '',
					mobile: '',
					code: '',
					password: '',
					nickname: '',
					invite: '',
					sfz_f: '',
					sfz_b: '',
					sfz_p: ''
				},
				rules: {
					mobile: [{
						required: true,
						message: '请输入手机号',
						trigger: ['blur', 'change']
					}, {
						type: 'number',
						len: 11,
						message: '手机号格式错误',
						trigger: ['blur']
					}, ],
					email: [{
						required: true,
						message: '请输入邮箱',
						trigger: ['blur', 'change']
					}, {
						type: 'email',
						message: '邮箱格式错误',
						trigger: ['blur']
					}, ],
					code: [{
						required: true,
						message: '请输入验证码',
						trigger: ['blur', 'change']
					}],
					password: [{
						required: true,
						message: '请输入密码',
						trigger: ['blur', 'change']
					}, {
						min: 6,
						message: '密码至少6位',
						trigger: ['blur']
					}, ],
					invite: [{
						required: true,
						message: '请输入邀请码',
						trigger: ['blur', 'change']
					}],
					nickname: [{
						required: true,
						message: '请输入用户名',
						trigger: ['blur', 'change']
					}],
					// sfz_f: [{
					// 	required: true,
					// 	message: '请上传身份证人像面',
					// 	trigger: ['change']
					// }],
					// sfz_b: [{
					// 	required: true,
					// 	message: '请上传身份证国徽面',
					// 	trigger: ['change']
					// }],
					// sfz_p: [{
					// 	required: true,
					// 	message: '请上传手持身份证',
					// 	trigger: ['change']
					// }],
				},
				invite: '',

				tips: '',

				sfz_f: [],
				sfz_b: [],
				sfz_p: []
			}
		},
		onLoad(e) {
			this.invite = e.invite || '';
			if (this.invite) {
				this.loginForm.invite = this.invite;
			}
		},
		methods: {
			gologin(){
				uni.redirectTo({
					url:"/pages/login/login"
				})
			},
			register() {
				this.$refs.loginForm.validate().then(res => {
					uni.$u.http.post('/api/user/register', this.loginForm).then(res => {
						if(res.code==1){
							uni.$u.toast('注册成功，审核中');
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
						email: this.loginForm.email
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
			// 新增图片
			async afterRead(event) {
				let lists = [].concat(event.file);
				let item = {
					...event.file,
					status: 'uploading',
					message: '上传中'
				};
				this[event.name] = [].concat(item);
				const res = await uni.$u.http.upload('/api/common/upload', {
					filePath: event.file.url,
					name: 'file',
				}).then(res => {
					if (res?.code == 1) {
						this[event.name].splice(0, 1, Object.assign(item, {
							status: 'success',
							message: '',
							url: res.data.fullurl
						}));
						this.loginForm[event.name] = res.data.url;
					} else {
						uni.$u.toast(res.msg || '上传失败');
						this[event.name] = [];
						this.loginForm[event.name] = '';
					}
					this.$refs.loginForm.validateField(event.name);
				}).catch(res => {
					this[event.name] = [];
					this.loginForm[event.name] = '';
				});
			},
			deletePic(event) {
				this[event.name].splice(event.index, 1);
				this.loginForm[event.name] = '';
			},
		}
	}
</script>

<style lang="scss" scoped>
	.page {
		width: 100vw;
		min-height: 100vh;
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: calc(var(--status-bar-height) + 100rpx) 60rpx 100rpx;
	}

	.welcome {
		font-size: 24px;
		font-weight: bold;
		margin-bottom: 60rpx;
	}

	.form {
		margin-bottom: 80rpx;
	}

	::v-deep .u-upload__button {
		background-color: #fff;
	}
	.loginbtn {
		margin-top: 40rpx;
	}
</style>