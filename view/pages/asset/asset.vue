<template>
	<view>
		<view class="head">
			<view class="flex">
				<view class="flex-1">
					<view class="yue">
						<view class="name">总资产</view>
						<view class="num">{{user.usdt || "0.0000" }}</view>
					</view>
				</view>
				<view class="flex-1">
					<view class="yue">
						<view class="name">冻结资产</view>
						<view class="num">{{user.usdt_dj || "0.0000" }}</view>
					</view>
				</view>
			</view>
			<view class="flex" style="margin-top: 30rpx;">
				<view class="flex-1">
					<u-button type="primary" plain shape="circle" @click="$u.route('/pages/recharge/add')">充币</u-button>
				</view>
				<view class="flex-1">
					<u-button type="primary" plain shape="circle" @click="$u.route('/pages/transfer/transfer')">转账</u-button>
				</view>
				<view class="flex-1" v-if="tbpay_switch==1">
					<u-button type="primary" plain shape="circle" @click="$u.route('/pages/withdraw/add')">提币</u-button>
				</view>
			</view>
		</view>
<!-- 		<view class="today">
			<view class="item">
				<view class="name u-info">今日售出</view>
				<view class="num">0.0000</view>
			</view>
			<view class="item u-border-left">
				<view class="name u-info">今日购买</view>
				<view class="num">0.0000</view>
			</view>
		</view> -->
		<u-modal :show="show" title="温馨提示" content='请绑定Letstalk账户' @confirm="confirm"></u-modal>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				show:false,
				tbpay_switch:0,
				user:{}
			}
		},
		onShow() {
			this.getUserInfo();
			this.getBaseConfig();
		},			
		methods: {
			confirm(){
				this.show = false
				uni.navigateTo({
					url:"/pages/user/info"
				})
			},			
			getUserInfo(){
				uni.$u.http.post('/api/user/getUserinfo').then((res) => {
					if(res.code == 1) {
						this.user = res.data
						if(!res.data.letstalk){
							this.show = true
						}
					}
				})
			},
			getBaseConfig(){
				uni.$u.http.post('/api/index/getBaseInfo').then((res) => {
					if(res.code == 1) {
						this.tbpay_switch = res.data.tbpay_switch
					}
				})
			}
		}
	}
</script>

<style lang="scss">
.head {
	padding: 30rpx;
	background-color: $u-primary-light;
}

.flex {
	display: flex;
	align-items: center;
}

.flex-1 {
	flex: 1;
}

.flex-1+.flex-1 {
	margin-left: 20rpx;
}

.yue {
	background-color: $u-primary;
	color: #fff;
	padding: 30rpx;
	border-radius: 20rpx;
	
	.name {
		font-size: 13px;
	}
	
	.num {
		margin-top: 20rpx;
		font-size: 18px;
	}
}

.today {
	background-color: #fff;
	padding: 30rpx 0;
	display: flex;
	
	.item {
		padding: 0 60rpx;
		flex: 1;
		
		.name {
			font-size: 12px;
		}
		
		.num {
			font-size: 18px;
			margin-top: 20rpx;
		}
	}
}
</style>
