<template>
	<view class="page">
		<view class="money">
			<view class="hdTitle">
				<view class="left">
					资产
				</view>
			</view>			
			<view class="box">
				<u-row justify="space-between" gutter="15" style="margin-top: 50rpx;">
					<u-col span="6">
						<view class="money-name">可用金额(USDT)</view>
						<view class="money-value">{{userInfo.usdt || "0.0000"}}</view>
					</u-col>
					<u-col span="6">
						<view class="money-name">冻结金额(USDT)</view>
						<view class="money-value">{{userInfo.usdt_dj || "0.0000"}}</view>
					</u-col>				
				</u-row>
				<u-row justify="space-between" gutter="15" style="margin-top: 50rpx;">
					<u-col span="6">
						<view class="money-name">兑入冻结金额(USDT)</view>
						<view class="money-value">0</view>
					</u-col>
					<u-col span="6">
						<view class="money-name">提币冻结金额(USDT)</view>
						<view class="money-value">0</view>
					</u-col>				
				</u-row>
			</view>
		</view>		
		<view class="money">
			<view class="hdTitle">
				<view class="left">
					汇率
				</view>
				<view class="right" @click="showBi = true">
					<view class="txt">{{biName}}</view>
					<u-icon name="arrow-down"></u-icon>
				</view>
			</view>
			<view class="box">
				<u-row justify="space-between" gutter="15">
					<u-col span="6">
						<view class="money-name">兑入</view>
						<view class="money-value">{{biData.duiru || '' }}</view>
					</u-col>
					<u-col span="6">
						<view class="money-name">兑出</view>
						<view class="money-value">{{biData.duichu || ''}}</view>
					</u-col>
				</u-row>
			</view>
		</view>
		<view v-if="open_team">
			<view class="money">
				<u-row justify="space-between" gutter="15">
					<u-col span="6">
						<view class="money-name">今日折扣</view>
						<view class="money-value">8.8888</view>
					</u-col>
					<u-col span="6">
						<view class="money-name">昨日折扣</view>
						<view class="money-value">9.9999</view>
					</u-col>
				</u-row>
			</view>
			<view class="head">团队成员</view>
			<view class="list">
				<view class="item" :class="{'u-border-top': index > 0}" v-for="(item,index) in 10" :key="index">
					<view class="flex">
						<view class="id">#168007</view>
						<view>
							<u-button type="primary" size="mini" plain shape="circle">设置</u-button>
						</view>
					</view>
					<view class="data">
						<view class="data-1">
							<view>出售费率：<text class="u-main-color">0%</text></view>
							<view>购买费率： <text class="u-main-color">0%</text></view>
						</view>
						<view class="data-1">
							<view>出售量： <text class="u-error">0.0000</text></view>
							<view>购买量： <text class="u-success">0.0000</text></view>
						</view>
						<view class="data-1 income">
							<view>我的收益</view>
							<view class="u-main-color">0.0000</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	
		<u-action-sheet
				:show="showBi"
				:actions="biArr"
				title="请选择币种"
				@close="showBi = false"
				@select="biSelect"
		>
		</u-action-sheet>
	
	</view>
</template>

<script>
	export default {
		data() {
			return {
				biArr:[],
				biData:{},
				biName:'CNY',
				showBi:false,
				open_team: false,
				userInfo:{},	
			}
		},
		onLoad() {
			this.getBiLst();
		},
		onShow() {
			this.getUserInfo();
			// this.open_team = uni.getStorageSync('open_team') ? true : false;
		},
		methods: {
			getUserInfo(){
				uni.$u.http.post('/api/user/getUserinfo').then(res => {
					if(res.code == 1) {
						this.userInfo =res.data;
					}
				})
			},			
			biSelect(e){
				this.biName = e.name				
				this.biArr.forEach(res=>{
					if(res.name==e.name){
						this.biData = res;
					}
				})
			},
			getBiLst(){
				uni.$u.http.post('/api/index/getBiLst', {
					id: this.id,
				}).then(res => {
					if (res.code == 1) {
						this.biArr = res.data
						res.data.forEach(res=>{
							if(res.default==1){
								this.biData = res;
							}
						})
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
	
	.head {
		font-weight: bold;
		margin-bottom: 20rpx;
	}
	
	.money {
		background-color: #fff;
		padding: 30rpx;
		border-radius: 10rpx;
		margin-bottom: 30rpx;
		.hdTitle {
			display:flex;
			justify-content: space-between;
			padding:10rpx 20rpx 30rpx 20rpx;
			border-bottom:#f0f0f0 1rpx solid;
			.left {
				font-size:30rpx;
				color:#888;
			}
			.right {
				display:flex;
				border:#d9d9d9 1rpx solid;
				font-size:24rpx;
				padding:6rpx 20rpx;
				color:rgba(0, 0, 0, 0.8);
				.txt {
					margin-right:10rpx;
				}
			}
		}
		.box {
			padding-top:20rpx;
		}
	}
	
	.money-name {
		color: #888;
		margin-bottom: 20rpx;
		text-align: center;
	}
	
	.money-value {
		font-size: 18px;
		color: #000;
		text-align: center;
	}
	
	.list {
		background-color: #fff;
		border-radius: 10rpx;
		
		.item {
			padding: 20rpx 30rpx;
			
			.flex {
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			
			.data {
				display: flex;
				font-size: 13px;
				margin-top: 20rpx;
				color: $u-info-dark;
				
				.data-1 {
					flex: 1;
				}
				
				.income {
					text-align: right;
				}
			}
		}
	}
</style>