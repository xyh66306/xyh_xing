<template>
	<view>
		<view class="list" v-if="lst.length>0">
			<view class="item"  v-for="(item,index) in lst" :key="index">
				<view class="row">
					<view class="name">{{item.orderid || ''}}</view>
					<view class="cny">{{item.usdt}}</view>
				</view>
				<view class="row u-info">
					<view class="time">
						<view style="display: inline-block;">
							<u-tag text="待审核" type="success" size="mini" plain v-if="item.pay_status==1"></u-tag>
							<u-tag text="已支付,待审核" type="success" size="mini" plain v-if="item.pay_status==2"></u-tag>
							<u-tag text="待平台审核" type="warning" size="mini" plain v-if="item.pay_status==3"></u-tag>
							<u-tag text="已完成"  size="mini" plain v-if="item.pay_status==4"></u-tag>
							<u-tag text="已取消" type="info" size="mini" plain v-if="item.pay_status==5"></u-tag>
						</view>
					</view>
					<view class="otc">金额 {{item.amount}}</view>
				</view>
				<view class="no u-border-top">
					<view>{{item.createtime}}</view>
					<view class="detail">
						<u-button type="primary" size="small" @click="$u.route('/pages/payment/sale?orderid='+item.orderid)">查看</u-button>
					</view>

				</view>
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
				loadStatus: 'more',
				pay_status:'',
				page:1,
				lst:[],
				user:{},
			}
		},
		onLoad() {
			this.getLst();
			this.user = uni.getStorageSync('user') || {};
		},
		methods: {
			getLst(){
				uni.$u.http.post('/api/rujin/index', {
					page: this.page,
					pay_status:this.pay_status
				}).then(res => {
					if (res.code == 1) {
						const _list = res.data.list;
						this.lst = [...this.lst, ..._list];
						if (res.data.count > this.lst.length) {
							this.loadStatus = 'more';
							this.page++;
						} else {
							// 数据已加载完毕
							this.loadStatus = 'noMore';
						}
					} else {
						console.log(res)
						// uni.$u.toast(res.msg);
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