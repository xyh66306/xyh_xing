<template>
	<view class="page">
		<view class="payinfo">
			<view class="u-info">发起者</view>
			<view class="flex u-border-bottom">
				<view>{{details.receive_name}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.receive_name)">复制</u-button>
				</view>
			</view>
			<view class="u-info">订单ID</view>
			<view class="flex u-border-bottom">
				<view>{{details.orderid}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.orderid)">复制</u-button>
				</view>
			</view>		
			<view class="u-info">实际订单数量</view>
			<view class="flex u-border-bottom">
				<view>{{details.act_num}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.act_num)">复制</u-button>
				</view>
			</view>	
			<view class="u-info">应支付</view>
			<view class="flex u-border-bottom">
				<view>{{details.amount}}</view>
				<view class="copy">
					<u-button type="primary" size="mini" plain @click="copy(details.amount)">复制</u-button>
				</view>
			</view>	
			<view class="u-info">汇率</view>
			<view class="flex u-border-bottom">
				<view>{{details.rate}}</view>
			</view>											
			<template v-if="details.pay_type==1">
				<view class="u-info">付款账户</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_account}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.bank_account)">复制</u-button>
					</view>
				</view>
				<view class="u-info">付款银行</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_name}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.bank_name)">复制</u-button>
					</view>
				</view>
				<view class="u-info">支行名称</view>
				<view class="flex u-border-bottom">
					<view>{{details.bank_zhihang}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.bank_zhihang)">复制</u-button>
					</view>
				</view>
			</template>
			<template v-else>
				<view class="u-info">付款账户</view>
				<view class="flex u-border-bottom">
					<view>{{details.pay_account}}</view>
					<view class="copy">
						<u-button type="primary" size="mini" plain @click="copy(details.pay_account)">复制</u-button>
					</view>
				</view>
				<view class="u-info">付款类型</view>
				<view class="flex u-border-bottom">
					<view>
						<template v-if="details.pay_type==1">银行卡</template>
						<template v-if="details.pay_type==2">支付宝</template>
						<template v-if="details.pay_type==3">微信</template>
					
					</view>
				</view>			
			</template>	
			<template v-if="details.pinzheng_image">
				<view class="u-info">付款记录</view>
				<view class="flex u-border-bottom">
					<view>
						<image :src="details.pinzheng_image" class="pay_ewm_img"></image>
					</view>
				</view>		
			</template>	
			<template v-if="details.ctime"> 
				<view class="u-info">发起时间</view>
				<view class="flex u-border-bottom">
					<view>{{details.ctime}}</view>
				</view>	
			</template>
		
			<view class="tips">
				<view>【禁止备注】禁止备注任何敏感字样</view>
				<view>【禁止他人代付】付款人必须是交易账户持有人</view>
			</view>
		</view>
		<u-button type="primary"  @click="$u.route('/pages/shensu/index?id='+details.orderid)">前往申诉</u-button>
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
				uni.$u.http.post('/api/cash/detail', {
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
		padding:20rpx;
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
</style>