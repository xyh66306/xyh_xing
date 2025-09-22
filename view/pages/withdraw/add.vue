<template>
	<view class="page">
		<view class="item">
			<view class="title">链名称</view>
			<view class="input">
				<view style="margin-right: 30rpx;" v-for="(item, index) in cfgs" :key="index">
					<u-tag :type="index == cfg ? 'primary' : 'info'" plain :text="item.name" @click="setCfg(index)"></u-tag>
				</view>
			</view>
		</view>		
<!-- 		<view class="item">
			<view class="title">收款方式</view>
			<view class="input">
				<view style="margin-right: 30rpx;" v-for="(item, index) in payType" :key="index">
					<u-tag :type="index == payIndex ? 'primary' : 'info'" plain :text="item" @click="setPayType(index)"></u-tag>
				</view>
			</view>
		</view>	 -->	
<!-- 		<view class="item">
			<view class="title">提币地址</view>
			<view class="input">
				<u-input v-model="addr" placeholder="请输入提币地址"></u-input>
			</view>
		</view> -->
		<view class="item">
			<view class="title">数量</view>
			<view class="input">
				<u-input type="number" v-model="num" placeholder="最小提币数量1">
					<template slot="suffix">
						<view class="flex">
							<view class="u-info">USDT</view>
							<view class="u-primary u-border-left" style="margin-left: 20rpx;padding-left: 20rpx;"
								@click="setAll">全部</view>
						</view>
					</template>
				</u-input>
			</view>
			<view class="tip">当前余额：<text class="u-primary">{{money}}</text></view>
		</view>
<!-- 		<view class="item">
			<view class="title">汇率</view>
			<view class="input">
				<u-input v-model="huilv" placeholder="当前汇率" readonly></u-input>
			</view>
		</view>		 -->
		<view class="item">
			<view class="title">手续费</view>
			<view class="input">
				<u-input v-model="fee" placeholder="备注内容" readonly>
					<template slot="suffix">
						<view class="u-info">USDT</view>
					</template>
				</u-input>
			</view>
			<view class="tip">预计到账：<text class="u-warning">{{ profit }}</text></view>
		</view>
		<view class="item">
			<view class="title">备注</view>
			<view class="input">
				<u-input v-model="remarks" placeholder="备注内容"></u-input>
			</view>
		</view>
		<u-button type="primary" @click="submit()">提币</u-button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				biArr:[],
				biData:{},
				biName:'TWD',
				addr: '',
				num: '',
				fee: 5,
				remarks: '',
				user:{},
				money:0,
				rate:7.21,
				huilv:0,
				profit:0,
				payType:[],
				payIndex:0,
				cfgs: [],
				cfg: 0,
			}
		},
		watch:{
			num(newValue,oldValue){
				this.profit = (newValue - this.fee).toFixed(4)
			}
		},
		onLoad() {
			this.getUserInfo();
			// this.getPayLst();
			// this.getBiLst();
			this.getCfg();
		},
		methods: {
			setType(e) {
				this.type = e;
			},
			setAll() {
				this.num = this.money
			},
			setPayType(index){
				this.payIndex = index
			},
			getCfg() {			
				uni.$u.http.post('/api/index/getRecharge',{
					diqu:this.user.diqu
				}).then(res => {
					if(res.code == 1) {
						// this.cfgs = res.data;
						res.data.forEach((e,index)=>{
							if(e.addr){
								this.cfgs.push(e)								
							}
							if(index==0){
								this.address = e.addr
							}
						})
					}else{
						uni.$u.toast(res.msg);
					}
				})
			},		
			setCfg(e) {
				this.cfg = e;
			},				
			async getUserInfo(){
				await uni.$u.http.post('/api/user/getUserInfo').then(res => {
					if (res.code == 1) {
						this.user = res.data;
						this.money = res.data.usdt
					} else {
						uni.$u.toast(res.msg);
					}
				
				}).catch(res => {
					console.log(res)
				});
			},
			submit(){
				if(this.money<=0){
					uni.$u.toast("请输入金额");
				}
				let type_ch = this.cfgs[this.cfg]
				let type = ''
				
				uni.$u.http.post('/api/user/withdraw', {
					pay_type: type,
					usdt:this.num,
					remarks:this.remarks,
				}).then(res => {
					if (res.code == 1) {
						this.getUserInfo();
						uni.$u.toast(res.msg);
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

	.item {
		margin-bottom: 30rpx;
	}

	.title {
		font-size: 14px;
		color: $u-info-dark;
		margin-bottom: 20rpx;
	}

	.input {
		display: flex;
		align-items: center;

	}

	.flex {
		display: flex;
		align-items: center;
	}

	.tip {
		margin-top: 10rpx;
		font-size: 14px;
		color: $u-info;
	}
</style>