<template>
	<view>
		<view class="list" v-if="lst.length>0">
			<view class="item">
				<u-row customStyle="margin-bottom: 10px" class="item-row" justify="space-between" align="center">
					 <u-col span="1">
						<view class="item-title">序号</view>
					</u-col>
					 <u-col span="2">
						<view class="item-title">订单数量</view>
					</u-col>	
					 <u-col span="2">
						<view class="item-title">返佣数量</view>
					</u-col>
					 <u-col span="2">
						<view class="item-title">超时</view>
					</u-col>						
					 <u-col span="2">
						<view class="item-title">状态</view>
					</u-col>	
					 <u-col span="3">
						<view class="item-title">时间</view>
					</u-col>												
				</u-row>						
			</view>
			<view class="item" v-for="(item,index) in lst" :key="index">
				<u-row customStyle="margin-bottom: 10px" class="item-row" justify="space-between" align="center" @click="$u.route('/pages/userrebate/yjdetails?id='+item.id)">
					 <u-col span="1">
						<view class="item-title">{{index+1}}</view>
					</u-col>
					 <u-col span="2">
						<view class="item-title">{{item.number}}</view>
					</u-col>	
					 <u-col span="2">
						<view class="item-title">{{item.money}}</view>
					</u-col>	
					 <u-col span="2">
						<view class="item-title">{{item.chaoshi==2?'是':'否'}}</view>
					</u-col>					
					 <u-col span="2">
						<view class="item-title">{{item.status==2?'待审核':'已通过'}}</view>
					</u-col>	
					 <u-col span="3">
						<view class="item-title">{{item.ctime}}</view>
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
				loadStatus: 'more',
				page:1,
				lst:[]
			}
		},
		onLoad(e) {
			this.getLst();
		},
		methods: {
			getLst(){
				uni.$u.http.post('/api/commission/index', {
					page: this.page,
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

<style>
	page {
		background: #fff;
	}
	.item {
		font-size: 14px;
		text-align: center;
	}
	.item-row {
		border-bottom:#f9f3f3 2rpx solid;
		height:70rpx;
		line-height:70rpx;
	}
	.item-title {
		text-align: center;
		overflow: hidden;
	}
</style>