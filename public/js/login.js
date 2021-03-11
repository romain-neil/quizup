const api_base = '/quizup/api';

if(typeof Element.prototype.clearChildren === 'undefined') {
	Object.defineProperty(Element.prototype, 'clearChildren', {
		configurable: true,
		enumerable: true,
		value: function() {
			while(this.firstChild) this.removeChild(this.lastChild)
		}
	});
}

function loadClass() {
	let epleID = document.getElementById("eple").value
	let d;

	fetch(`${api_base}/class?lyc=${epleID}`)
		.then(data => data.json())
		.then(res => {
			d = res[0]
		})
		.finally(() => {
			let select = document.getElementById('class_list');
			select.clearChildren()

			for(const [id, nom] of Object.entries(d.response)) {
				let opt = document.createElement('option');
				opt.value = id;
				opt.append(nom)

				select.append(opt)
			}
		})
}
