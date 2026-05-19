function setInitialdate(el) {
	el.valueAsDate = new Date();
}
setInitialdate(document.querySelector(".sac-date"));

const rowContainer = document.getElementById("row-container");
const addButton = document.getElementById("add-row");

// should always be 1, but let's count for funsies
let rowCount = document.querySelectorAll(".entry-row").length;

// toggle to numbers if sacrament is confession
rowContainer.addEventListener("change", evt => {
	if (evt.target.classList.contains("sac-type")) {
		const nameInput = evt.target.closest(".entry-row").querySelector(".sac-name-or-number");
		if (evt.target.value === "Confession") {
			nameInput.type = "number";
			nameInput.min = 1;
			nameInput.value = 1;
		}
		else {
			nameInput.type = "text";
			nameInput.min = null;
			nameInput.value = "";
		}
	}
});

addButton.onclick = () => {
	const rows = document.querySelectorAll(".entry-row");
	const lastRow = rows[rows.length - 1];
	const newRow = lastRow.cloneNode(true);

	rowCount++;

	const locationField = newRow.querySelector(".sac-location");
	locationField.id = `sac-location__${rowCount}`;

	// keep date, type, and location; clear out value and notes
	//    (this is basically for when I'm doing multiple baptisms)
	const lastType = lastRow.querySelector(".sac-type").value;
	newRow.querySelector(".sac-type").value = lastType;

	const newNameOrNumber = newRow.querySelector(".sac-name-or-number");
	if (newNameOrNumber.type === "number") {
		newNameOrNumber.value = 1;
	}
	else {
		newNameOrNumber.value = "";
	}
	newRow.querySelector("input[name='sac-notes[]']").value = "";
	newRow.querySelector(".remove-row").style.display = "inline-block";

	rowContainer.appendChild(newRow);

	initAutoComplete(locationField);

	newNameOrNumber.focus();
};

rowContainer.onclick = (evt) => {
	if (evt.target.classList.contains("remove-row")) {
		evt.target.closest(".entry-row").remove();
	}
};
