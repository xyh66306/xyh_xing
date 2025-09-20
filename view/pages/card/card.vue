<template>
	<view class="container">
		<!-- 页面标题 -->
		<text class="page-title">选择收款方式</text>

		<!-- 收款列表 -->
		<scroll-view class="payment-list" scroll-y>
			<view class="payment-item" v-for="(item, index) in ewmLst" :key="index" @click="handleItemClick(item)">
				<!-- 收款图标 -->
				<template v-if="item.pay_skpt == 'alipay'">
					<view class="payment-icon" style="background:#1677ff">
						<u-icon name="zhifubao" size="32" color="#fff"></u-icon>
					</view>
					<view class="payment-info">
						<text class="payment-name">支付宝</text>
						<text class="payment-account">
							账号：<text class="account-mask">{{ item.pay_nums }}</text>
						</text>
					</view>
					<view class="payment-actions"></view>
				</template>
				<template v-else>
					<view class="payment-icon" style="background:#07c160">
						<u-icon name="weixin-fill" size="32" color="#fff"></u-icon>
					</view>
					<view class="payment-info">
						<text class="payment-name">微信支付</text>
						<text class="payment-account">
							状态：<text class="account-mask">{{ item.status=='hidden'?'关闭':'开启' }}</text>
						</text>
					</view>
					<view class="payment-actions"></view>					
				</template>

			</view>
			<view class="payment-item payment-item-bank" v-for="(vo, index) in bankLst" :key="vo.id+100" @click="handleBankClick(vo)">
			
				<view class="payment-icon" style="background:#ff6b6b">
					<u-icon name="rmb-circle-fill" size="32" color="#fff"></u-icon>
				</view>
				<view class="payment-info">
					<text class="payment-name">{{vo.bank_name}}</text>
					<text class="payment-account">
						状态：<text class="account-mask">{{ vo.status=='hidden'?'关闭':'开启' }}</text>
					</text>
				</view>
				<view class="payment-actions"></view>
			</view>	
		</scroll-view>


		<!-- 添加收款账户按钮 -->
		<view class="add-account-btn" @click="handleAddAccount">
			<u-icon name="plus-circle" size="24" color="#fff"></u-icon>
			<text>添加收款账户</text>
		</view>

		<!-- uView 弹出层 -->
		<u-popup :show="isPopupShow" mode="bottom" round="24" :border-radius="24" :safe-area-inset-bottom="true">
			<!-- 弹出层内容 -->
			<view class="popup-content">
				<!-- 弹出层标题 -->
				<view class="popup-header">
					<text class="popup-title">添加收款账户</text>
					<u-icon name="close-circle" size="24" color="#999" @click="isPopupShow = false"></u-icon>
				</view>

				<!-- 收款方式列表 -->
				<scroll-view class="popup-list" scroll-y>
					<!-- 支付宝 -->
					<view class="popup-item" @click="handleClick('alipay')" v-if="showAddAlipay">
						<view class="popup-icon alipay-icon">
							<u-icon name="zhifubao" size="32" color="#fff"></u-icon>
						</view>
						<view class="popup-info">
							<text class="popup-name">支付宝</text>
							<text class="popup-tip">支持个人账号</text>
						</view>
						<u-icon name="arrow-right" size="20" color="#ccc"></u-icon>
					</view>

					<!-- 微信支付 -->
					<view class="popup-item" @click="handleClick('wxpay')" v-if="showAddWxpay">
						<view class="popup-icon wechat-icon">
							<u-icon name="weixin-fill" size="32" color="#fff"></u-icon>
						</view>
						<view class="popup-info">
							<text class="popup-name">微信支付</text>
							<text class="popup-tip">支持个人微信</text>
						</view>
						<u-icon name="arrow-right" size="20" color="#ccc"></u-icon>
					</view>

					<!-- 银行卡 -->
					<view class="popup-item" @click="handleClick('bank')" v-if="showAddBankpay">
						<view class="popup-icon bank-icon">
							<u-icon name="rmb-circle-fill" size="32" color="#fff"></u-icon>
						</view>
						<view class="popup-info">
							<text class="popup-name">银行卡</text>
							<text class="popup-tip">支持储蓄卡</text>
						</view>
						<u-icon name="arrow-right" size="20" color="#ccc"></u-icon>
					</view>
				</scroll-view>
			</view>
		</u-popup>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				ewmLst:[],
				bankLst:[],
				isPopupShow: false,
				showAddAlipay:true,
				showAddWxpay:true,
				showAddBankpay:true,
				statusarr:['开启','关闭'],
			}
		},
		onLoad() {
			this.getEwmLst();
			this.getBankLst();
		},
		methods: {
			getEwmLst(){
				let that = this
				uni.$u.http.post('/api/user/getpayLst').then(res => {
					if(res.code == 1) {
						let data = res.data
						data.forEach(item=>{
							if(item.pay_skpt == 'alipay') {
								that.showAddAlipay = false
							}else if(item.pay_skpt == 'wxpay'){
								that.showAddWxpay = false
							}
						})
						that.ewmLst = data
						
					}
				})	
			},
			getBankLst(){
				let that = this
				uni.$u.http.post('/api/user/getbankcardLst').then(res => {
					if(res.code == 1) {
						that.bankLst = res.data
					}
				})	
			},			
			// 添加收款账户
			handleAddAccount() {
				this.isPopupShow = true
			},
			handleClick(type) {
				if (type == 'alipay') {
					uni.navigateTo({
						url: '/pages/card/ewm?pay_skpt=2'
					})
				} else if (type == 'wxpay') {
					uni.navigateTo({
						url: '/pages/card/ewm?pay_skpt=1'
					})
				} else {
					uni.navigateTo({
						url: '/pages/card/bank'
					})
				}
			},
			handleItemClick(item) {
				if (item.pay_skpt == 'alipay') {
					uni.navigateTo({
						url: '/pages/card/ewm?pay_skpt=2'
					})
				} else if (item.pay_skpt == 'wxpay') {
					uni.navigateTo({
						url: '/pages/card/ewm?pay_skpt=1'
					})
				}
			},
			handleBankClick(item) {
				uni.navigateTo({
					url: '/pages/card/bank?id='+item.id
				})
			},
			close() {
				this.isPopupShow = false
			}
		}
	}
</script>

<style lang="scss" scoped>
	.container {
		min-height: 100vh;
		background: #f5f7fa;
		padding: 32rpx 30rpx 120rpx;
		/* 底部预留按钮空间 */
	}

	.page-title {
		font-size: 32rpx;
		font-weight: 600;
		color: #1a1a1a;
		margin-bottom: 48rpx;
		display: block;
	}

	.payment-list {
		max-height: calc(100vh - 200rpx);
		box-sizing: border-box;
	}

	.payment-item {
		background: #fff;
		border-radius: 20rpx;
		padding: 30rpx;
		display: flex;
		align-items: center;
		gap: 32rpx;
		box-shadow: 0 8rpx 30rpx rgba(0, 0, 0, 0.03);
		margin-bottom: 20rpx;
		transition: background 0.2s;

		&:active {
			background: #f8f9fa;
		}
	}

	.payment-icon {
		width: 80rpx;
		height: 80rpx;
		border-radius: 20rpx;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.payment-info {
		flex: 1;

		.payment-name {
			font-size: 34rpx;
			font-weight: 500;
			color: #1a1a1a;
			display: block;
			margin-bottom: 8rpx;
		}

		.payment-account {
			font-size: 28rpx;
			color: #666;
			display: flex;
			align-items: center;
			gap: 16rpx;

			.account-mask {
				color: #999;
			}
		}
	}

	.payment-actions {
		display: flex;
		gap: 24rpx;

		.copy-btn,
		.select-btn {
			display: flex;
			align-items: center;
			gap: 12rpx;
			border-radius: 16rpx;
			font-size: 28rpx;
			padding: 12rpx 20rpx;
		}

		.copy-btn {
			background: #f8f9fa;
			color: #666;

			&:active {
				background: #eee;
			}
		}

		.select-btn {
			background: #1677ff;
			color: #fff;

			&.active {
				background: #0958d9;
			}

			&:active {
				opacity: 0.9;
			}
		}
	}

	.add-account-btn {
		position: fixed;
		bottom: 40rpx;
		left: 30rpx;
		right: 30rpx;
		background: #1677ff;
		color: #fff;
		border-radius: 32rpx;
		padding: 32rpx 24rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 24rpx;
		font-size: 32rpx;
		font-weight: 500;
		box-shadow: 0 12rpx 40rpx rgba(22, 119, 255, 0.25);
		z-index: 100;
		transition: all 0.2s;

		&:active {
			transform: translateY(-4rpx);
			box-shadow: 0 16rpx 48rpx rgba(22, 119, 255, 0.35);
		}
	}


	
	

/* 弹出层样式 */
.popup-content {
  background: #fff;
  border-radius: 24rpx 24rpx 0 0;
  padding: 40rpx 30rpx;
  min-height: 400rpx;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32rpx;

  .popup-title {
    font-size: 34rpx;
    font-weight: 600;
    color: #1a1a1a;
  }
}

.popup-list {
  max-height: 500rpx;
  box-sizing: border-box;
}

.popup-item {
  display: flex;
  align-items: center;
  gap: 24rpx;
  padding: 24rpx 0;
  border-bottom: 1rpx solid #f5f5f5;

  &:last-child {
    border-bottom: none;
  }

  &:active {
    background: #f8f9fa;
  }
}

.popup-icon {
  width: 80rpx;
  height: 80rpx;
  border-radius: 16rpx;
  display: flex;
  justify-content: center;
  align-items: center;

  &.alipay-icon {
    background: #1677ff;
  }

  &.wechat-icon {
    background: #07c160;
  }

  &.bank-icon {
    background: #ff6b6b;
  }
}

.popup-info {
  flex: 1;

  .popup-name {
    font-size: 32rpx;
    font-weight: 500;
    color: #1a1a1a;
    display: block;
    margin-bottom: 8rpx;
  }

  .popup-tip {
    font-size: 26rpx;
    color: #999;
  }
}	
</style>