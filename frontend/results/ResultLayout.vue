<template>
	<body class="has-navbar-fixed-top">
		<canvas id="backgroundCanvas"></canvas>
		<div v-if="loaded">
			<nav-bar
				:routes="routes"
				class="is-dperiwinkle has-periwinkle-underline"
			>
				<template v-slot:title>
					<router-link to="/" style="color: inherit;">
						<h1 class="is-size-4">/r/anime Awards</h1>
					</router-link>
				</template>
			</nav-bar>
			<router-view/>
			<footer class="hero-foot footer has-background-dperiwinkle has-text-light">
				<div class="container has-text-centered">
					<small class="has-text-light">
						The r/anime Awards are a rag tag group of volunteers passionate about showcasing the shows we love. As such, we do not own or claim ownership of
						any of the shows, characters, or images showcased on this site.
						All copyrights and/or trademarks of any character and/or image used belong to their respective owner(s). Please don't sue us.
					</small>
					<br>
					<br>
					<br>
					<router-link to="/about" style="color:inherit">
						<a class="footerLink has-text-light">About/Credits</a>
					</router-link>
				</div>
			</footer>
		</div>
	</body>
</template>

<script>
import NavBar from '../common/NavBar.vue';
import {mapState, mapActions} from 'vuex';
const navbarRoutes = require('../common/navbar-routes');

export default {
	components: {
		NavBar,
	},
	data () {
		return {
			routes: null,
			loaded: false,
		};
	},
	computed: {
		...mapState(['locks']),
	},
	methods: {
		...mapActions(['getLocks']),
	},
	async mounted () {
		if (!this.locks) {
			await this.getLocks();
		}
		const allocLock = this.locks.find(lock => lock.name === 'allocations');
		const guideLock = this.locks.find(lock => lock.name === 'juryGuide');
		const ongoingLock = this.locks.find(lock => lock.name === 'awards-ongoing');
		if (ongoingLock.flag) {
			this.routes = navbarRoutes.ongoingRoutes;
		} else {
			this.routes = navbarRoutes.resultsRoutes;
		}
		if (guideLock.flag) {
			this.routes.push({name: 'Jury Guide / FAQ', path: '/juryguide'});
		}
		if (allocLock.flag) {
			this.routes.push({name: 'Allocations', path: '/allocations'});
		}
		this.loaded = true;

		// BACKGROUND STUFF
		(function() {

			var canvas, ctx, circ, nodes, mouse, SENSITIVITY, SIBLINGS_LIMIT, DENSITY, NODES_QTY, ANCHOR_LENGTH, MOUSE_RADIUS;

			// how close next node must be to activate connection (in px)
			// shorter distance == better connection (line width)
			SENSITIVITY = 150;
			// note that siblings limit is not 'accurate' as the node can actually have more connections than this value that's because the node accepts sibling nodes with no regard to their current connections this is acceptable because potential fix would not result in significant visual difference 
			// more siblings == bigger node
			SIBLINGS_LIMIT = 6;
			// default node margin
			DENSITY = 100;
			// total number of nodes used (incremented after creation)
			NODES_QTY = 0;
			// avoid nodes spreading
			ANCHOR_LENGTH = 400;
			// highlight radius
			MOUSE_RADIUS = 500;

			circ = 2 * Math.PI;
			nodes = [];

			canvas = document.querySelector('#backgroundCanvas');

			resizeWindow();
			mouse = {
				x: canvas.width / 2,
				y: canvas.height / 2,
				vx: 1,
				vy: 1,
			};

			ctx = canvas.getContext('2d');
			if (!ctx) {
				alert("Ooops! Your browser does not support canvas :'(");
			}

			function Node(x, y) {
				this.anchorX = x;
				this.anchorY = y;
				this.x = Math.random() * (x - (x - ANCHOR_LENGTH)) + (x - ANCHOR_LENGTH);
				this.y = Math.random() * (y - (y - ANCHOR_LENGTH)) + (y - ANCHOR_LENGTH);
				this.vx = Math.random() * 2 - 1;
				this.vy = Math.random() * 2 - 1;
				this.energy = Math.random() * 100;
				this.radius = Math.random();
				this.siblings = [];
				this.brightness = 0;
			}

			Node.prototype.drawNode = function() {
				var color = "rgba(231, 169, 36, " + this.brightness + ")";
				ctx.beginPath();
				ctx.arc(this.x, this.y, 2 * this.radius + 2 * this.siblings.length / SIBLINGS_LIMIT, 0, circ);
				ctx.fillStyle = color;
				ctx.fill();
			};

			Node.prototype.drawConnections = function() {
				for (var i = 0; i < this.siblings.length; i++) {
				var color = "rgba(231, 169, 36, " + this.brightness + ")";
				ctx.beginPath();
				ctx.moveTo(this.x, this.y);
				ctx.lineTo(this.siblings[i].x, this.siblings[i].y);
				ctx.lineWidth = 1 - calcDistance(this, this.siblings[i]) / SENSITIVITY;
				ctx.strokeStyle = color;
				ctx.stroke();
				}
			};

			Node.prototype.moveNode = function() {
				this.energy -= 2;
				if (this.energy < 1) {
					this.energy = Math.random() * 800;
					if (this.x - this.anchorX < -ANCHOR_LENGTH) {
						this.vx = Math.random() * 0.5;
					} else if (this.x - this.anchorX > ANCHOR_LENGTH) {
						this.vx = Math.random() * -0.5;
					} else {
						this.vx = Math.random() * 1 - 0.5;
					}
					if (this.y - this.anchorY < -ANCHOR_LENGTH) {
						this.vy = Math.random() * 0.5;
					} else if (this.y - this.anchorY > ANCHOR_LENGTH) {
						this.vy = Math.random() * -0.5;
					} else {
						this.vy = Math.random() * 1 - 0.5;
					}
				}
				this.x += this.vx * this.energy / 800;
				this.y += this.vy * this.energy / 800;
			};

			function initNodes() {
				ctx.clearRect(0, 0, canvas.width, canvas.height);
				nodes = [];
				for (var i = 0; i < canvas.width+DENSITY*2; i += DENSITY) {
				for (var j = 0; j < canvas.height+DENSITY*2; j += DENSITY) {
					nodes.push(new Node(i, j));
					NODES_QTY++;
				}
				}
			}

			function calcDistance(node1, node2) {
				return Math.sqrt(Math.pow(node1.x - node2.x, 2) + (Math.pow(node1.y - node2.y, 2)));
			}

			function findSiblings() {
				var node1, node2, distance;
				for (var i = 0; i < NODES_QTY; i++) {
				node1 = nodes[i];
				node1.siblings = [];
				for (var j = 0; j < NODES_QTY; j++) {
					node2 = nodes[j];
					if (node1 !== node2) {
					distance = calcDistance(node1, node2);
					if (distance < SENSITIVITY) {
						if (node1.siblings.length < SIBLINGS_LIMIT) {
						node1.siblings.push(node2);
						} else {
						var node_sibling_distance = 0;
						var max_distance = 0;
						var s;
						for (var k = 0; k < SIBLINGS_LIMIT; k++) {
							node_sibling_distance = calcDistance(node1, node1.siblings[k]);
							if (node_sibling_distance > max_distance) {
							max_distance = node_sibling_distance;
							s = k;
							}
						}
						if (distance < max_distance) {
							node1.siblings.splice(s, 1);
							node1.siblings.push(node2);
						}
						}
					}
					}
				}
				}
			}

			function redrawScene() {
				resizeWindow();
				ctx.clearRect(0, 0, canvas.width, canvas.height);
				findSiblings();
				var i, node, distance;
				for (i = 0; i < NODES_QTY; i++) {
				node = nodes[i];
				distance = calcDistance({
					x: mouse.x,
					y: mouse.y
				}, node);
				if (distance < MOUSE_RADIUS) {
					node.brightness = 1 - distance / MOUSE_RADIUS;
				} else {
					node.brightness = 0;
				}
				}
				for (i = 0; i < NODES_QTY; i++) {
				node = nodes[i];
				if (node.brightness) {
					// node.drawNode();
					node.drawConnections();
				}
				node.moveNode();
				}

				mouse.x += mouse.vx;
				mouse.y += mouse.vy;

				if (mouse.x < 0 || mouse.x > canvas.width) {
					mouse.vx *= -1;
				}
				if (mouse.y < 0 || mouse.y > canvas.height) {
					mouse.vy *= -1;
				}

				requestAnimationFrame(redrawScene);
			}

			function initHandlers() {
				document.addEventListener('resize', resizeWindow, false);
				// document.addEventListener('mousemove', mousemoveHandler, false);
			}

			function resizeWindow() {
				canvas.width = window.innerWidth;
				canvas.height = window.innerHeight;
			}

			function mousemoveHandler(e) {
				mouse.x = e.clientX;
				mouse.y = e.clientY;
			}

			initHandlers();
			initNodes();
			redrawScene();

		})();
	},
};
</script>

<style lang="scss" scoped>
@use "../styles/utilities.scss" as *;

body {
	height: 100vh;
	display: flex;
	flex-direction: column;
}
.navbar {
	flex: 0 0 auto;
}
.breadcrumb-wrap {
	padding: 0.75rem;
}
.full-height-content {
	height: calc(100vh - 6.25rem);
	overflow: auto;
	@include mobile {
		height: auto;
	}
}
canvas {
  position: fixed;
  z-index: -9999;
  top: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
  background-color: $dark;
  overflow: hidden;
}
</style>
