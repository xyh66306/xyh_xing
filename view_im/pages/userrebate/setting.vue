<template>
	<view class="">
		<view class="h3">常规-交易员</view>
		<view class="tab" v-if="myjylist.length>0">
			<view class="th">
				<view class="td">支付方式</view>
				<view class="td">币</view>
				<view class="td">资金流向</view>
				<view class="td">最低</view>
				<view class="td">最高</view>
				<view class="td">返佣比例</view>
			</view>		
			<view class="tr" v-for="(vo,index) in myjylist" :key="index">
				<view class="td">{{vo.type}}</view>
				<view class="td">{{vo.bi}}</view>
				<view class="td">{{vo.churu}}</view>
				<view class="td">{{vo.min_usdt}}</view>
				<view class="td">{{vo.max_usdt}}</view>
				<view class="td">{{vo.rate}}</view>
			</view>					
		</view>
		<view class="tabEmpty" v-else>
			<navigator url="/pages/card/card">请先设置收款信息</navigator>
		</view>
		<view class="h3 mt20">推荐人返佣比例</view>
		<u-sticky>
			<view class="states">
				<u-subsection :list="states" mode="button" :current="state" @change="setState"></u-subsection>
			</view>
			<view class="search">
				<u-search v-model="value" placeholder="请输入推荐人邮箱"  @change="change" @custom="custom"  @search="search" :shape="shape" :clearabled="clearabled"
				:show-action="showAction" :input-align="inputAlign" @clear="clear"></u-search>
			</view>
		</u-sticky>	
		<view class="tab" v-if="recomjylist.length>0">
			<view class="th">
				<view class="td">昵称</view>
				<view class="td">支付方式</view>
				<view class="td">币</view>
				<view class="td">返佣比例</view>
				<view class="td">操作</view>
			</view>		
			<view class="tr" v-for="(vo,index) in recomjylist" :key="index">
				<view class="td">{{vo.nickname}}</view>
				<view class="td">{{vo.type}}</view>
				<view class="td">{{vo.bi}}</view>
				<view class="td">{{vo.rate}}%</view>
				<view class="td" @click="showPop(index)">编辑</view>
			</view>					
		</view>	
		<view class="tabEmpty" v-else>
			暂无信息
		</view>	
					
		<u-popup :show="show"  mode="center" @close="cancelPop">
			<view class="popBox">
				<view class="head">{{setInfo.nickname}}返佣设置</view>
				<view class="code">
					<u-input type="number" placeholder="返佣设置比例[0,99]" v-model="rate"></u-input>
				</view>
				<view class="btnLst flex">
					<u-button type="info" @click="cancelPop">取消</u-button>
					<u-button type="primary" @click="rateSet" style="margin-left: 20rpx;">确定</u-button>
				</view>
			</view>
		</u-popup>
					
					
					
	</view>
</template>

<script>
	export default {
		data() {
			return {
				value: '',
				shape: 'square',
				clearabled: true,
				showAction: true,
				inputAlign: 'left',
				states: ['兑出', '兑入'],
				state: 0,
				recomjylist:[],
				myjylist:[],
				page:1,
				loadStatus: 'more',	
				show: false,
				rate:'',
				setInfo:{}
			}
		},
		onLoad() {
			this.getMyRebate();
			this.getRecomLst();
		},
		methods: {
			setState(e) {
				this.state = e;
				this.recomjylist = [];
				this.page = 1;
				this.loadStatus = 'more'
				this.getRecomLst()
			},	
			change(value) {
			},
			custom(value) {
				this.recomjylist = [];
				this.page = 1;
				this.value =value
				this.loadStatus = 'more'
				this.getRecomLst()
			},					
			search(value) {
				this.recomjylist = [];
				this.page = 1;
				this.value =value
				this.loadStatus = 'more'
				this.getRecomLst()
			},
			showPop(index) {
				this.rate = this.recomjylist[index].rate;
				this.setInfo = this.recomjylist[index];
				this.show = true;
			},			
			cancelPop() {
				this.rate = '';
				this.setInfo =[];
				this.show = false;
			},	
			getMyRebate(){
				uni.$u.http.post('/api/rebate/index').then(res => {
					if(res.code == 1) {
						this.myjylist = res.data;
					}
				})	
			},
			getRecomLst(){
				uni.$u.http.post('/api/rebate/getRecomRebate',{
					page:this.page,
					email:this.value,
					churu:this.state==0?'duichu':'duiru'
				}).then(res => {
					if(res.code == 1) {
						const _list = res.data.data;
						this.recomjylist = [...this.recomjylist, ..._list];
						if (res.data.count > this.recomjylist.length) {
							this.loadStatus = 'more';
							this.page++;
						} else {
							// 数据已加载完毕
							this.loadStatus = 'noMore';
						}
					}
				})	
			},
			rateSet(){
				let that = this;
				if(that.rate>100 || that.rate<0){
					return uni.$u.toast('请设置0-99的比例');
				}
				uni.$u.http.post('/api/rebate/setting',{
					id:that.setInfo.id,
					rate:that.rate
				}).then(res => {
					if(res.code == 1) {
						that.cancelPop();
						this.recomjylist = [];
						this.page = 1;
						this.loadStatus = 'more'
						this.getRecomLst()
						return uni.$u.toast('设置成功');
					} else {
						that.cancelPop();
						return uni.$u.toast('设置失败');
					}
					
				})
			}
		}
	}
</script>

<style lang="scss">
	
	.popBox {
		width: 80vw;
		padding: 60rpx;		
		.head {
			text-align: center;
			font-size: 18px;
			font-weight: bold;
			margin-bottom: 30rpx;
		}		
		.code {
			margin-bottom: 30rpx;
		}
	}
	.btnLst {
		width:100%;
		display:flex;
		justify-content: space-around;
		align-items: center;
		padding:0 20rpx;
	}
	
	.states {
		padding: 20rpx 30rpx;
		background-color: #fff;
	}
	.search {
		padding: 20rpx 30rpx;
		background-color: #fff;
	}
	.h3 {
		font-size:34rpx;
		background-color: #fff;
		padding: 20rpx 30rpx;
		border-bottom:#f5f5f5 2rpx solid;
	}
	.mt20 {
		margin-top:20rpx;
	}
	.tab {
		background-color: #fff;
		.th {
			display: flex;
			line-height:80rpx;
			height:80rpx;
			border-bottom:#f5f5f5 4rpx solid;
			text-align: center;
			font-size:24rpx;
			.td {
				flex:1;
			}
		}
		.tr {
			display: flex;
			line-height:70rpx;
			height:70rpx;
			border-bottom:#f5f5f5 2rpx solid;
			text-align: center;
			font-size:24rpx;
			.td {
				flex:1;
			}
		}		
	}
	.tabEmpty {
		line-height:80rpx;
		height:80rpx;
		background: #fff;
		border-bottom:#f5f5f5 4rpx solid;
		text-align: center;
		font-size:24rpx;
	}
</style>