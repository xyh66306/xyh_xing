<template>
	<view>
		<u-sticky>
			<view class="states">
				<u-subsection :list="states" mode="button" :current="state" @change="setState"></u-subsection>
			</view>
		</u-sticky>

		<view class="list" v-if="lst.length>0">
			<view class="item"  v-for="(item,index) in lst" :key="index">
				<view class="row">
					<view class="name">{{item.receive_name}}</view>
					<u-tag text="待确认" type="primary" size="mini" plain v-if="item.pay_status<=2"></u-tag>
					<u-tag text="待审核" type="info"  size="mini" plain v-if="item.pay_status==3"></u-tag>
					<u-tag text="已完成" type="success" size="mini" plain v-if="item.pay_status==4"></u-tag>
					<u-tag text="已取消" type="error" size="mini" plain v-if="item.pay_status==5"></u-tag>
					<view class="cny u-primary">￥{{item.amount}}</view>
				</view>
				<view class="row u-info">
					<view class="time">{{item.orderid}}</view>
					<view class="otc">数量 {{item.usdt}} </view>
				</view>
				<view class="no u-border-top">
					<view>{{item.createtime}}</view>
					<view class="detail">
						<u-button type="primary" size="small" @click="$u.route('/pages/payment/sale?orderid='+item.orderid)">查看</u-button>
					</view>

				</view>
			</view>
			<view class="totalArea">
				<u-row customStyle="margin-bottom: 10px">
					<u-col span="6">
						<text class="u-line-1" type="info">累计金额：{{amount_count}}</text>
					</u-col>
					<u-col span="6">
						<text class="u-line-1" type="primary">累计USDT：{{user_usdt_count}}</text>
					</u-col>
				</u-row>
			</view>			
		</view>
		<view class="empty" v-else>
			<u-empty mode="order" icon="/static/order.png"></u-empty>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				states: ['全部','待确认','待审核','已完成','申诉'], //1抢单2确认3（已取消，不显示）4申诉
				loadStatus: 'more',
				state: '',
				page:1,
				lst:[],
				amount_count:0,
				user_usdt_count:0,				
			}
		},		
		onLoad() {
			this.getLst();
		},		
		methods: {
			setState(e) {
				if(this.state !== e){
					this.page =1;	
					this.state = e;
					this.lst =[];
					this.getLst();
				}
			},
			getLst(){
				
				// let pay_status = 0;
				// if(this.state==0){
				// 	pay_status =1;
				// }else if(this.state==1){
				// 	pay_status =2;
				// }else if(this.state==2){
				// 	pay_status =4;
				// }
				
				uni.$u.http.post('/api/rujin/getOrderLst', {
					page: this.page,
					pay_status:this.state
				}).then(res => {
					if (res.code == 1) {
						const _list = res.data.list;
						this.user_usdt_count = res.data.user_usdt_count
						this.amount_count = res.data.amount_count
						this.lst = [...this.lst, ..._list];
						if (res.data.count > this.lst.length) {
							this.loadStatus = 'more';
							this.page++;
						} else {
							// 数据已加载完毕
							this.loadStatus = 'noMore';
						}
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			}			
		},
		onReachBottom() {
			if (this.loadStatus === 'more') {
				this.getLst();
			}
		},		
	}
</script>

<style lang="scss">
	
.totalArea {
	font-size: 28rpx;
	margin-top: 30rpx;
	padding: 0 30rpx;
}	
	
	.states {
		padding: 20rpx 30rpx;
		background-color: #fff;
	}

	.list {
		padding: 20rpx 30rpx;
	}

	.item {
		background-color: #fff;
		padding: 20rpx 30rpx;
		border-radius: 10rpx;
		box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
	}

	.item+.item {
		margin-top: 20rpx;
	}

	.row {
		display: flex;
		align-items: center;
		font-size: 13px;
	}

	.row {
		margin-bottom: 20rpx;
	}

	.name {
		font-size: 18px;
		margin-right: 20rpx;
	}

	.cny {
		margin-left: auto;
		font-size: 18px;
	}

	.time {
		flex: 1;
	}

	.no {
		padding-top: 20rpx;
		display: flex;
		align-items: center;
	}

	.detail {
		margin-left: auto;
	}
</style>