<template>
	<view>
		<u-sticky>
			<view class="states">
				<u-subsection :list="states" mode="button" :current="state" @change="setState"></u-subsection>
			</view>
		</u-sticky>
		
		<view class="list" v-if="lst.length>0">
			<view class="item" v-for="(item,index) in lst" :key="index">
				<view class="row">
					<view class="name">{{item.orderid}}</view>
					<u-tag :text="item.pay_status_txt" size="mini" plain v-if="item.pay_status==1"></u-tag>
					<u-tag :text="item.pay_status_txt" type="success" size="mini" plain v-if="item.pay_status>=2 && item.pay_status<5"></u-tag>
					<u-tag :text="item.pay_status_txt" type="success" size="mini" plain v-if="item.pay_status==5"></u-tag>
					<u-tag :text="item.pay_status_txt" type="warning" size="mini" plain v-if="item.pay_status==6"></u-tag>
					
					<view class="cny u-primary">￥{{item.withdrawAmount}}</view>
				</view>
				<view class="row u-info">
					<view class="time">{{item.createtime}}</view>
					<view class="otc">数量 {{item.usdt}} </view>
				</view>
				<view class="no u-border-top">
					<view>{{item.orderid}}</view>
					<view class="detail">
						<u-button type="primary" size="small" @click="$u.route('/pages/payment/payment?orderid='+item.orderid)">查看</u-button>
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
				states: ['全部','抢单中','审核中', '已完成'],
				loadStatus: 'more',
				state: '',
				page:1,
				lst:[],
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
				uni.$u.http.post('/api/chujin/orderin', {
					page: this.page,
					pay_status:this.state
				}).then(res => {
					if (res.code == 1) {
						// this.lst = res.data.list
						
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
	box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
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
