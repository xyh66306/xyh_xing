<template>
	<view class="page">
		<view class="cards">
			<u-swipe-action>
				<u-swipe-action-item :options="options" @click="alipayclick">
					<view class="card u-border-bottom">
						<view class="icon">
							<u-icon name="zhifubao-circle-fill" size="30" color="primary"></u-icon>
						</view>
						<view>支付宝账号</view>
					</view>
				</u-swipe-action-item>
				<u-swipe-action-item :options="options" @click="wxclick">
					<view class="card u-border-bottom">
						<view class="icon">
							<u-icon name="weixin-circle-fill" size="30" color="success"></u-icon>
						</view>
						<view>微信账号</view>
					</view>
				</u-swipe-action-item>
				<u-swipe-action-item :options="options" @click="bankclick">
					<view class="card u-border-bottom">
						<view class="icon">
							<u-icon name="rmb-circle-fill" size="30" color="warning"></u-icon>
						</view>
						<view>银行卡</view>
					</view>
				</u-swipe-action-item>
			</u-swipe-action>
		</view>
		<view class="add">
			<u-button type="primary" size="large" shape="circle">添加收款账户</u-button>
		</view>
		
	</view>
</template>

<script>
	export default {
		data() {
			return {
				options: [{
					text: '编辑',
					style: {
						backgroundColor: '#3c9cff'
					}
				}, {
					text: '删除',
					style: {
						backgroundColor: '#f56c6c'
					}
				}],
			}
		},
		methods: {		
			alipayclick(e){
				if(e.index==0){
					uni.navigateTo({
						url:'/pages/card/ewm?pay_skpt=2'
					})
				} else {
					console.log("del");
				}
			},
			wxclick(e){
				if(e.index==0){
					uni.navigateTo({
						url:'/pages/card/ewm?pay_skpt=1'
					})
				} else {
					console.log("del");
				}
			},
			bankclick(e){
				if(e.index==0){
					uni.navigateTo({
						url:'/pages/card/bank'
					})
				} else {
					console.log("del");
				}
			},						
		}
	}
</script>

<style lang="scss">
	page {
		padding: 30rpx 0;
	}
	
	.cards {
		margin-bottom: 30rpx;
	}

	.card {
		padding: 30rpx;
		display: flex;
		align-items: center;
	}
	
	.icon {
		margin-right: 20rpx;
	}
	
	.add {
		padding: 30rpx;
	}
</style>