<template>
	<view class="content">
		<view class="bg">
			<image src="/static/topbg.jpg"></image>
		</view>
		<view class="logo">
			<image :src="logo" mode=""></image>
		</view>
		<view class="logo_name">
			KYC
		</view>
		<view class="btn" @click="downApp">
			下载安装
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: 'Hello',
				downurl:'',
				logo:''
			}
		},
		onLoad() {
			this.getApp();
		},
		methods: {
			getApp(){
				uni.request({
					url: 'https://bingocn.wobeis.com/api/down/index',
					method: 'POST',
					data: {
						id:1
					},
					success: res => {
						let response =res.data
						if(response.code==1){							
							this.logo = response.data.image
							this.downurl = response.data.downloadurl
						}
					},
				});
			},
			downApp(){
				// 检测是否为微信浏览器
				const isWechat = navigator.userAgent.toLowerCase().includes('micromessenger');
				if (isWechat) {
					uni.showModal({
						title: '提示',
						content: '微信浏览器不支持下载，请用微信扫二维码在外部浏览器打开',
						showCancel: false,
						confirmText: '知道了'
					});
					return;
				}
				
				// 检测是否为iOS设备
				const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
				if (isIOS) {
					uni.showModal({
						title: '提示',
						content: 'iOS设备暂不支持下载',
						showCancel: false,
						confirmText: '知道了'
					});
					return;
				}
				
				// 如果不是微信浏览器且不是iOS设备，则执行下载
				if (this.downurl) {
					window.location.href = this.downurl;
				}
			}
		}
	}
</script>

<style>
image {
	width: 100%;
	height: 100%;
}	
.bg {
	width: 100%;
	height: 60rpx;
}
.logo {
	width: 260rpx;
	height: 260rpx;
	display: flex;
	justify-content: center;
	align-items: center;
	background-color: #fff;
	box-shadow: 0 0 20rpx rgba(0, 0, 0, .2);
	border-radius: 50%;
	overflow: hidden;
	margin: 100rpx auto;
	padding:50rpx;
	image {
		width: 220rpx;
		height: 220rpx;
		border-radius: 20rpx;
		overflow: hidden;
	}
}
.logo_name {
	font-size: 46rpx;
	text-align: center;
	margin: 30rpx auto;
}
.btn {
	border-radius: 10rpx;
	background-color: #2c56ff;
	width: 200rpx;
	display: flex;
	height:60rpx;
	justify-content: center;
	align-items: center;
	color: #fff;
	margin: 50rpx auto;
	font-size: 24rpx;
	line-height: 60rpx;
}
</style>
