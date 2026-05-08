<template>
	<view class="page">
		<view class="payinfo">
			<view class="u-info">订单编号</view>
			<view class="flex u-border-bottom">
				<view>{{details.bianhao}}</view>
			</view>
			<view class="u-info">数量</view>
			<view class="flex u-border-bottom">
				<view>{{details.num}}</view>
			</view>	
			<view class="u-info">手续费</view>
			<view class="flex u-border-bottom">
				<view>{{details.fee}}</view>
			</view>	
			<view class="u-info">哈希值</view>
			<view class="flex u-border-bottom">
				<view>{{details.hash}}</view>
			</view>											
			<view class="u-info">凭证</view>
			<view class="flex u-border-bottom">
				<view>
					<image :src="details.pz_image" class="pay_ewm_img"></image>
				</view>
			</view>
			<template v-if="details.ctime"> 
				<view class="u-info">发起时间</view>
				<view class="flex u-border-bottom">
					<view>{{details.ctime}}</view>
				</view>	
			</template>
		
		</view>


		
	</view>
</template>

<script>
	export default {
		data() {
			return {
				id:'',
				details:{},
			}
		},
		onLoad(e) {
			this.getPayStatus(e.id);
		},
		methods: {
			getPayStatus(id){
				let that = this
				uni.$u.http.post('/api/recharge/detail', {
					id,
				}).then(res => {
					if (res.code == 1) {
						that.details = res.data
					}				
				}).catch(res => {
					// console.log(res)
				});
			},
		}
	}
</script>

<style lang="scss">
	.page {
		padding:20rpx;
	}
	.u-border-bottom {
		overflow: hidden;
	}
	.payinfo {
		padding: 30rpx;
		background-color: #fff;
		border-radius: 10rpx;
		margin-bottom: 30rpx;

		.flex {
			display: flex;
			align-items: center;
			padding: 20rpx 0;
			margin-bottom: 30rpx;

			.copy {
				margin-left: auto;
			}
		}

		.tips {
			font-size: 14px;
			color: $u-error;
		}
	}
	.pay_ewm_img {
		width:230rpx;
		height:230rpx;
	}
	.popArea {
		padding-top:50rpx;
		.pop_head {
			padding:0 20rpx;
			text-align:center;
			font-size:32rpx;
		}
		.upload {
			width:300rpx;
			height:300rpx;
			margin:0 auto;
		}
	}
</style>