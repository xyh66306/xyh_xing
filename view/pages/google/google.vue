<template>
	<view class="page">
		<view class="google">
			<u-image :src="qr" width="160" height="160"></u-image>
			<view class="secret" @click="copySecret">
				<view>{{secret}}</view>
				<u-icon name="share-square" size="20" color="primary" style="margin-left: 10rpx;"></u-icon>
			</view>
		</view>
		<view class="tips u-info-dark">
			<view class="tip">1.在Google Authenticator应用程序中，点击+号，选择“手动输入验证码”或“扫码条形码”</view>
			<view class="tip">2.在打开的页面中输入你的账户名和上方的密钥（务必开启基于时间）</view>
			<view class="tip">3.点击完成，你将获得一个6位数的验证码，填入下方谷歌验证码输入框</view>
		</view>
		<view class="form">
			<view class="input">
				<u-input type="number" v-model="code" placeholder="请输入邮箱验证码">
					<template slot="suffix">
						<u-code ref="uCode" @change="codeChange"></u-code>
						<u-button @tap="getCode" :text="tips" type="primary" size="mini"></u-button>
					</template>
				</u-input>
			</view>
			<view class="input">
				<u-input type="number" v-model="google_code" placeholder="请输入谷歌验证码"></u-input>
			</view>
			<u-button type="primary" @click="save">确定</u-button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				secret: '',
				qr: '',
				
				email: '',
				code: '',
				google_code: '',
				
				tips: '',
			}
		},
		onLoad() {
			this.getQr();
			let user = uni.getStorageSync('user');
			this.email = user?.email;
		},
		methods: {
			getQr() {
				uni.$u.http.post('/api/google/get').then(res => {
					if (res.code == 1) {
						this.secret = res.data.secret;
						this.qr = res.data.qr;
					}
				})
			},
			copySecret() {
				uni.setClipboardData({
					data: this.secret,
					success: () => {
						uni.$u.toast('已复制');
					}
				})
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
						email: this.email,
						event: 'google'
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
			save() {
				if(!this.code) {
					return uni.$u.toast('请填写邮箱验证码');
				}
				if(!this.google_code) {
					return uni.$u.toast('请填写谷歌验证码');
				}
				uni.$u.http.post('/api/google/bind', {
					code: this.code,
					google_code: this.google_code,
					secret: this.secret
				}).then(res => {
					if(res.code == 1) {
						uni.showModal({
							content: '绑定成功',
							showCancel: false,
							success: () => {
								uni.navigateBack();
							}
						})
					}else{
						uni.$u.toast(res.msg);
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.page {
		padding: 30rpx;
	}

	.google {
		width: 100%;
		margin-bottom: 30rpx;
		display: flex;
		flex-direction: column;
		align-items: center;
		background-color: #fff;
		padding: 30rpx;
		border-radius: 10rpx;

		.secret {
			background-color: $u-primary-light;
			padding: 20rpx 30rpx;
			border-radius: 10rpx;
			color: $u-primary;
			display: flex;
			align-items: center;
			flex-grow: 10px;
			margin-top: 20rpx;
		}
	}

	.tips {
		font-size: 14px;
		margin-bottom: 30rpx;

		.tip+.tip {
			margin-top: 10rpx;
		}
	}
	
	.form {
		background-color: #fff;
		padding: 30rpx;
		border-radius: 10rpx;
		
		.input {
			margin-bottom: 30rpx;
		}
	}
</style>