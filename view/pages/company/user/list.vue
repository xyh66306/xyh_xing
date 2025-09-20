<template>
	<view>
		<view class="tab" v-if="lst.length>0">
			<view class="tr">
				<view class="td">序号</view>
				<view class="td">状态</view>
				<view class="td">昵称</view>
				<view class="td">所属团队</view>
				<view class="td">接单开关</view>
			</view>
			<view class="tr items" v-for="(item,index) in lst" :key="index">
				<view class="td">{{index+1}}</view>
				<view class="td">{{item.status_txt}}</view>
				<view class="td">{{item.nickname}}</view>
				<view class="td">{{item.teamname}}</view>
				<view class="td">{{item.pay_status=='hidden'?'关闭':'开启'}}</view>
			</view>
		</view>		
		<view class="empty" v-else>
			<u-empty mode="list" icon="/static/order.png"></u-empty>
		</view>
	</view>
</template>

<script>
	export default{
		data(){
			return {			
				loadStatus: 'more',
				page:1,
				lst:[]
			}
		},
		onLoad() {
			this.getTeams();
		},
		methods:{
			getTeams(){
				uni.$u.http.post('/api/user/getTeamLst').then(res=>{
					if(res.code == 1) {
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
					}
				})

			}
		},
		onReachBottom() {
			if (this.loadStatus === 'more') {
				this.getTeams();
			}
		},
	}
</script>

<style lang="scss">
page {
	background:#fff;
}
	.tab {
		// width:710rpx;
		// margin: 0 auto;
		background:#fff;
		.tr {
			display: flex;
			height:70rpx;
			line-height:70rpx;
			.td {
				flex:1;
				text-align: center;
				border-bottom: #f3f3f3 2rpx solid;
				font-size:28rpx;
				overflow: hidden;
			}
		}
		.items {
			font-size:24rpx;
			.caozuo {
				display:flex;
				justify-content: center;
				.czbtn {
					margin-right:16rpx;
				}
			}
		}
	}
</style>