<template>
	<view>
		<view class="searchArea">
			<u-search placeholder="请输入订单编号" v-model="orderid"></u-search>
			<view class="sslst">
				<view class="items" @click="zonghe()">
					<view class="ss_txt">综合</view>
					<u-icon name="arrow-down" size="10"></u-icon>
				</view>
				<view class="items" @click="showBi = true">
					<view class="ss_txt">{{biName}}</view>
					<view class="icon">
						<u-icon name="arrow-up" size="10" top="2"></u-icon>
						<u-icon name="arrow-down" size="10" top="-2"></u-icon>
					</view>
				</view>	
				<view class="items" @click="showPayType = true">
					<view class="ss_txt">{{payTpeyName}}</view>
					<view class="icon">
						<u-icon name="arrow-up" size="10" top="2"></u-icon>
						<u-icon name="arrow-down" size="10" top="-2"></u-icon>
					</view>
				</view>	
				<view class="items" @click="showStatus = true">
					<view class="ss_txt">{{statusName}}</view>
					<view class="icon">
						<u-icon name="arrow-up" size="10" top="2"></u-icon>
						<u-icon name="arrow-down" size="10" top="-2"></u-icon>
					</view>
				</view>	
				<view class="items">
					<view class="ss_txt">筛选</view>
					<view class="icon">
						<u-icon name="arrow-down" size="10"></u-icon>
					</view>
				</view>															
			</view>
		</view>
		<view class="list" v-if="lst.length>0">
			
			<view class="item" v-for="(item,index) in lst" :key="index" @click="gotodetails(item.orderid)">
				<view class="row">
					<view class="name">{{item.receive_name || ''}}</view>
					<u-tag text="抢单中" type="success" size="mini" plain v-if="item.pay_status==0"></u-tag>
					<u-tag text="抢单中" type="success" size="mini" plain v-if="item.pay_status==1"></u-tag>
					<u-tag text="已抢单"  size="mini" plain v-if="item.pay_status==2"></u-tag>
					<u-tag text="已支付，待审核"  size="mini" plain v-if="item.pay_status==3"></u-tag>
					<u-tag text="已支付，待审核"  size="mini" plain v-if="item.pay_status==4"></u-tag>
					<u-tag text="已完成" type="success" size="mini" plain v-if="item.pay_status==5"></u-tag>
					<view class="cny u-primary">￥{{item.withdrawAmount}}</view>
				</view>
				<view class="row u-info">
					<view class="time">{{item.createtime}}</view>
					<view class="otc">数量 {{item.usdt}}</view>
				</view>
				<view class="no u-border-top">{{item.orderid}}</view>
			</view>
			
		</view>
		<view class="empty" v-else>
			<u-empty mode="order" icon="/static/order.png"></u-empty>
		</view>
		
		<u-action-sheet
				:show="showBi"
				:actions="biArr"
				title="请选择币种"
				@close="showBi = false"
				@select="biSelect"
		>
		</u-action-sheet>
		
		
		<u-action-sheet
				:show="showPayType"
				:actions="payArr"
				title="请选择支付方式"
				@close="showPayType = false"
				@select="paySelect"
		>
		</u-action-sheet>		
		
		<u-action-sheet
				:show="showStatus"
				:actions="statusArr"
				title="请选择订单状态"
				@close="showStatus = false"
				@select="statusSelect"
		>
		</u-action-sheet>			
		
	</view>
</template>

<script>
	export default {
		data() {
			return {
				orderid:'',
				loadStatus: 'more',
				pay_status:'',
				page:1,
				lst:[],
				biArr:[],
				biName:'币种',
				showBi:false,
				where:{},
				payTpeyName:'支付方式',
				showPayType:false,
				payArr:[
					{
						index:1,
						name:'银行卡',
					},
					{
						index:2,
						name:'支付宝',
					},
					{
						index:3,
						name:'微信',
					}					
				],
				statusName:'状态',
				showStatus:false,				
				statusArr:[
					{
						index:0,
						name:'抢单',
					},
					{
						index:1,
						name:'进行中',
					},				
				]				
			}
		},
		onLoad() {
			// this.getBiLst(); //所有币类型
			// this.getLst();
		},	
		onShow() {
			this.page=1;
			this.getLst();
		},			
		methods: {
			zonghe(){
				this.lst = [];
				this.where = {};
				this.page=1;
				this.getLst();
			},
			biSelect(e){
				if(e.name !=this.biName){
					this.biName = e.name
					this.lst = [];
					this.where.bi_type = e.name;
					this.page=1;
					this.getLst();
				}
			},
			paySelect(e){
				if(e.name !=this.payTpeyName){
					this.payTpeyName = e.name
					this.lst = [];
					this.where.pay_type = e.index;
					this.page=1;
					this.getLst();
				}
			},
			statusSelect(e){
				if(e.name !=this.statusName){
					this.statusName = e.name
					this.lst = [];
					this.where.pay_status = e.index;
					this.page=1;
					this.getLst();
				}
			},			
			getLst(){
				let where = this.where;
				where['page'] = this.page
				uni.$u.http.post('/api/chujin/index',where).then(res => {
					if (res.code == 1) {
						// const _list = res.data.list;
						this.lst = res.data.list;
						// if (res.data.count > this.lst.length) {
						// 	this.loadStatus = 'more';
						// 	this.page++;
						// } else {
						// 	// 数据已加载完毕
						// 	this.loadStatus = 'noMore';
						// }
					} else {
						console.log(res)
						// uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			},
			gotodetails(orderid){
				uni.navigateTo({
					url:'/pages/payment/payment?orderid='+orderid
				})
			},
			//所有币类型
			getBiLst(){
				uni.$u.http.post('/api/index/getBiLst', {
					id: this.id,
				}).then(res => {
					if (res.code == 1) {
						this.biArr = res.data
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
.searchArea {
	padding:20rpx 20rpx;
	background:#fff;
	margin-top:10rpx;
}	

.sslst {
	display:flex;
	padding-top:20rpx;
	.items {
		display:flex;
		justify-content: center;
		align-items: center;
		flex:1;
		font-size:24rpx;
		color:#999;
		.ss_txt {
			margin-right:10rpx;
		}
		.icon{
			display:flex;
			flex-direction: column;
			margin-top: 4rpx;
		}
	}
}
	
.empty {
	width: 100vw;
	height: 100vh;
	display: flex;
	flex-direction: column;
	justify-content: center;
	padding-bottom: 20vh;
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
}

</style>