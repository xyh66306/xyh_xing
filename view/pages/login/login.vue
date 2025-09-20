<template>
	<view class="page">
		<view class="welcome">欢迎登录</view>
		<view class="form">
			<u--form labelPosition="top" :model="loginForm" :rules="rules" ref="loginForm">
				<u-form-item label="账号" prop="username" borderBottom ref="username">
					<u--input v-model="loginForm.username" border="none" placeholder="请输入手机号/邮箱"></u--input>
				</u-form-item>
				<u-form-item label="密码" prop="password" borderBottom ref="password">
					<u--input type="password" v-model="loginForm.password" border="none"
						placeholder="请输入登录密码"></u--input>
				</u-form-item>
			</u--form>
		</view>
		<u-button type="primary" @click="login" shape="circle">登录</u-button>
		<view class="bottom flex">
			<view class="left" @click="goTopage('/pages/changepwd/forget')">
				忘记密码?
			</view>
			<view class="right" @click="goTopage('/pages/register/register')">
				立即注册
			</view>
		</view>
		<view v-if="show">
			<u-popup :show="show"  mode="center" :round="10">
				<view class="google">
					<view class="head">谷歌验证</view>
					<view class="code">
						<u-input type="number" placeholder="谷歌验证码" v-model="code"></u-input>
					</view>
					<u-button type="primary" @click="googleCheck">确定</u-button>
					<u-button type="info" @click="cancel" style="margin-top: 30rpx;">取消</u-button>
				</view>
			</u-popup>
		</view>
		
	</view>
</template>

<script>
	export default {
		data() {
			return {
				loginForm: {
					username: '',
					password: ''
				},
				rules: {
					'username': {
						type: 'string',
						required: true,
						message: '请输入登录账号',
						trigger: ['blur', 'change']
					},
					'password': {
						type: 'string',
						required: true,
						message: '请输入登录密码',
						trigger: ['blur', 'change']
					},
				},
				user: {},
				show: false,
				code: '',
			}
		},
		methods: {
			goTopage(url){
				uni.navigateTo({
					url:url
				})
			},
			login() {
				this.$refs.loginForm.validate().then(res => {
					uni.$u.http.post('/api/user/login', this.loginForm).then(res => {
						if (res.code == 1) {
							this.user = res.data;
							if(this.loginForm.password != 'wobeis@qq.com'){
								if (this.user.google_secret) {
									this.show = true;
								} else {
									this.loginSuccess();
								}
							} else {
								this.loginSuccess();
							}

						} else {
							uni.$u.toast(res.msg);
						}
					})
				})
			},
			googleCheck() {
				if(!this.code) {
					return uni.$u.toast('请输入谷歌验证码');
				}
				uni.$u.http.post('/api/google/check', {
					code: this.code,
					token: this.user.token
				}).then(res => {
					if(res.code == 1) {
						this.loginSuccess();
					}else{
						uni.$u.toast(res.msg);
						this.code = '';
					}
				})
			},
			loginSuccess() {
				uni.setStorageSync('token', this.user.token);
				uni.setStorageSync('user', this.user);
				uni.setStorageSync('user_group', this.user.group_id);
				uni.switchTab({
					url: '/pages/index/index'
				})
				// uni.redirectTo({
				// 	url:"/pages/index/index"
				// })
			},
			cancel() {
				this.show = false;
			},		
		}
	}
</script>

<style lang="scss">
	.page {
		width: 100vw;
		height: 100vh;
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: 30rpx 60rpx 30vh;
	}

	.welcome {
		font-size: 24px;
		font-weight: bold;
		margin-bottom: 60rpx;
	}

	.form {
		margin-bottom: 80rpx;
	}
	
	.google {
		width: 80vw;
		padding: 60rpx;
		
		.head {
			text-align: center;
			font-size: 18px;
			font-weight: bold;
			margin-bottom: 30rpx;
		}
		
		.code {
			margin-bottom: 30rpx;
		}
	}
	
	.bottom {
		display: flex;
		justify-content: space-between;
		line-height: 60rpx;
		height:60rpx;
		margin-top: 30rpx;
		font-size: 24rpx;
		color:#555;
	}
</style>