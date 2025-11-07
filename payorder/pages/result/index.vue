<template>
	<view class="content">
		<view class="content-bg" >
			<view class="bg bg-common"></view>
			<view class="result-body">
				<view class="result-body-title">
					<view class="body-title-left">
						<image src="/static/images/icon.png"></image>
					</view>
					<view class="body-title-right">
						<view class="">
							支付成功!
						</view>
						<view class="">
							感谢您的购买
						</view>
					</view>
				</view>
				<view class="result-body-detail">
					<view class="body-detail-btm">
						<view class="">
							下单时间：{{ paymentInfo.pay_time }}
						</view>
						<view class="">
							支付金额：{{ paymentInfo.usdt }} USDT
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
const base_url = "http://ceshiotc.wobeis.com"
export default {
	data() {
		return {
			paymentInfo: {
			}, 
			orderId: 0,
			access_key:'',
		};
	},
	onLoad(options) {
		if(options.orderid){
			this.orderid = options.orderid
			this.access_key = options.access_key
			this.getDetails();
		}
	},
	methods: {
		getDetails() {
			let that = this
			uni.request({
				url: base_url + '/openapi/details/index',
				method: "POST",
				data: {
					orderid:that.orderid,
					access_key:that.access_key
				},
				success: (res) => {
					if (res.data.code == 1) {
						that.paymentInfo = res.data.data
					}
				}

			})
		}
	}
};
</script>

<style>
.content-bg {
	position: relative;
	padding-top: 150rpx;
}
.bg-common {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 400rpx;
}
.bg {
	background: #ff6600;
}
.fail-bg {
	background: linear-gradient(rgb(254, 142, 90),rgb(254, 142, 150));
}

.result-body {
	position: relative;
	z-index: 100;
}
.result-body-title {
	display: flex;
	align-items: center;
	justify-content: center;
	color: #FFFFFF;
	margin-bottom:20rpx;
	height: 230rpx;
}
.body-title-left {
	width: 162rpx;
	height: 110rpx;
}
.body-title-left image {
	width: 100%;
	height: 100%;
}
.body-title-right {
	font-size: 36rpx;
	font-family: PingFang SC;
	font-weight: bold;
	color: #FFFFFF;
	line-height: 60rpx;
}
.body-title-right view:last-child {
	font-size: 28rpx;
}
.result-body-detail {
	width: 100%;
	height: 465rpx;
	background: url("/static/images/bg2.png") no-repeat;
	background-size: 100% 100%;
	padding: 80rpx 60rpx 0;
	color: #333333;
}
.result-fail-detail {
	width: 100%;
	height: 465rpx;
	background: url("/static/images/bg3.png") no-repeat;
	background-size: 100% 100%;
	padding: 80rpx 60rpx 0;
	color: #999999;
}
.body-detail-price {
	font-size: 42rpx;
	font-family: PingFang SC;
	font-weight: bold;
	text-align: center;
	padding-bottom: 50rpx;
	border-bottom: 1rpx solid #F2F3F5;
	margin-bottom: 50rpx;
}

.body-detail-btm {
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	line-height: 60rpx;
}
.result-btn button {
	width: 420rpx;
	height: 86rpx;
	border-radius: 43rpx;
	font-size: 28rpx;
	font-family: PingFang SC;
	font-weight: 500;
	color: #FFFFFF;
	text-align: center;
	line-height: 86rpx;
	margin: 50rpx auto;
}
.result-btn .success-btn {
	background: #006955;
}

.result-btn .fail-btn {
	background: linear-gradient(rgb(254, 142, 90),rgb(254, 142, 150));
}

.result {
	text-align: center;
	padding-top: 200upx;
}

.result-img {
	width: 140upx;
	height: 140upx;
	margin-bottom: 20upx;
}

.result-num {
	color: #666;
	font-size: 30upx;
	margin-bottom: 20upx;
}

.result-top { 
	color: #666;
	font-size: 30upx;
	margin-bottom: 20upx;
}

.result-mid {
	margin-bottom: 60upx;
}

.result-bot .btn {
	margin-top: 40upx;
	font-size: 26upx;
	padding: 0 50upx;
}
</style>
