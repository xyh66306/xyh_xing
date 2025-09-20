<template>
	<view>
		<template v-if="lst.length>0">
			<u-cell-group>
				<u-cell :title="(index+1) + '、' + item.nickname" :value="item.email" v-for="(item,index) in lst" :key="index"></u-cell>
			</u-cell-group>
		</template>
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
</style>