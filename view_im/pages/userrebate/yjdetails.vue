<template>
	<view class="page">
		<view class="payinfo">
			<view class="u-info">发起者</view>
			<view class="flex u-border-bottom">
				<view>{{details.nickname}}</view>
			</view>
			<view class="u-info">关联P4B订单号</view>
			<view class="flex u-border-bottom">
				<view>{{details.p4b_orderid}}</view>
			</view>
			<view class="u-info">返佣订单号</view>
			<view class="flex u-border-bottom">
				<view>{{details.fy_orderid}}</view>
			</view>						
			<view class="u-info">订单数量</view>
			<view class="flex u-border-bottom">
				<view>{{details.number}}</view>
			</view>
			<view class="u-info">返佣比例</view>
			<view class="flex u-border-bottom">
				<view>{{details.rate}}</view>
			</view>		
			<view class="u-info">返佣类型</view>
			<view class="flex u-border-bottom">
				<view>{{details.type}}</view>
			</view>	
			<view class="u-info">层级</view>
			<view class="flex u-border-bottom">
				<view>{{details.level}}</view>
			</view>	
			<view class="u-info">状态</view>
			<view class="flex u-border-bottom">
				<view>{{details.status}}</view>
			</view>
			<view class="u-info">是否超时</view>
			<view class="flex u-border-bottom">
				<view>{{details.chaoshi}}</view>
			</view>		
			<view class="u-info">创建时间</view>
			<view class="flex u-border-bottom">
				<view>{{details.ctime}}</view>
			</view>	
								
			<view class="u-info">审核时间</view>
			<view class="flex u-border-bottom">
				<view>{{details.utime}}</view>
			</view>									
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				id:'',
				details:{}
			}
		},
		onLoad(e) {
			if(e.id){
				this.id = e.id
				this.getDetails()
			}
		},
		methods: {
			copy(text){
				uni.setClipboardData({
					data:text,
				})
			},
			getDetails(){
				uni.$u.http.post('/api/Commission/detail', {
					id: this.id,
				}).then(res => {
					if (res.code == 1) {
						this.details = res.data
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			}
		}
	}
</script>

<style lang="scss">
	.page {
		padding: 30rpx;
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
</style>