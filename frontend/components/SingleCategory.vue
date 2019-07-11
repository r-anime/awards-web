<template>
	<div class="section">
		<div class="level title-margin">
			<div class="level-left">
				<div class="level-item">
					<h2 class="title">Category name</h2>
				</div>
			</div>
			<div class="level-right">
				<div class="level-item">
					<div class="tabs is-toggle">
						<ul>
							<li>
								<a>yeet</a>
							</li>
							<li class="is-active">
								<a>other yeet</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<keep-alive>
			<router-view/>
		</keep-alive>
	</div>
</template>

<script>
export default {
	props: ['id'],
	data () {
		return {
			category: null,
		};
	},
	mounted () {
		fetch(`/api/category/${this.id}`).then(async response => {
			const json = await response.json();
			if (!response.ok) {
				throw json.error;
			}
			this.category = json;
		}).catch(window.alert);
	},
};
</script>
