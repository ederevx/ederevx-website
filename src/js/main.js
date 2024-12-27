import * as con from "./common/container.js";
import * as presets from "./common/presets.js";

const local_props = {
	name: "main",
}

const about = {
	name: "about",
	container: con.create_div("about", presets.container.class_set_1),
}

const projects = {
	name: "projects",
	container: con.create_div("projects", presets.container.class_set_1),
}

const contact = {
	name: "contact",
	container: con.create_div("contact", presets.container.class_set_1),
}

export const props = {
	name: local_props.name,
	container: con.create_fluid(local_props.name, "main"),
}

export function set_content(element) {
	element.innerHTML = get_about_content() + get_projects_content() + get_contact_content();
}

function generate_main_section_content(section, content) {
	return `<br id=${section.name}>` + con.generate_html_content(section.container, content);
}

const get_about_content = function() {
	return generate_main_section_content(about, about_content);
}

const get_projects_content = function() {
	return generate_main_section_content(projects, projects_content);
}

const get_contact_content = function() {
	return generate_main_section_content(contact, contact_content);
}

let about_content = `
	<div class="container-fluid" id="ac-title">
		<h1 class="display-3">About</h1>
	</div>
	<article id="ac-body">
		<div class="my-4 container-fluid" id="ac-headline">
		<h2 class="display-5 fs-2">Mainframe Computer Operator and Open-source Software Programmer</h2>
		<p class="fs-5 text-muted">Based on Tallahassee, Florida, United States</p>
		</div> <!-- ac-headline -->
		<div class="my-4 container-fluid" id="ac-headline-caption">
		A mainframe computer operator studying computer engineering and software development. Highly adaptive with technologies, capable of working with or without a team under pressure and experienced with programming and troubleshooting various computer systems and software.
		</div> <!-- ac-headline-caption -->
		<div class="my-4 container-fluid" id="ac-workexp">
		<button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#ac-workexp-content" aria-expanded="false" aria-controls="ac-workexp-content">
			<h2 class="display-5 fs-2">Work Experience</h2>
		</button>
		<div class="collapse" id="ac-workexp-content">
			<div class="row mt-2" id="ac-wc-row">
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">Computer Operator III (Mainframe)</h5>
				<h6 class="card-subtitle mb-2 text-muted">Florida Department of Financial Services</h6>
				<p class="card-text">March 2024 - Present</p>
				</div>
			</div>
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">Computer Systems Analyst</h5>
				<h6 class="card-subtitle mb-2 text-muted">The Florida Lottery</h6>
				<p class="card-text">August 2023 - March 2024</p>
				</div>
			</div>
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">Computer Systems Operator</h5>
				<h6 class="card-subtitle mb-2 text-muted">The Florida Lottery</h6>
				<p class="card-text">May 2023 - August 2023</p>
				</div>
			</div>
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">Neonatal Intensive Care Unit Secretary</h5>
				<h6 class="card-subtitle mb-2 text-muted">Tallahassee Memorial HealthCare</h6>
				<p class="card-text">February 2023 - July 2023</p>
				</div>
			</div>
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">Restaurant Crew Member</h5>
				<h6 class="card-subtitle mb-2 text-muted">McDonald's</h6>
				<p class="card-text">August 2022 - February 2023</p>
				</div>
			</div>
			</div> <!-- ac-wc-row -->
		</div> <!-- ac-workexp-content -->
		</div> <!-- ac-workexp -->
		<div class="my-4 container-fluid" id="ac-education">
		<button class="btn btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#ac-education-content" aria-expanded="false" aria-controls="ac-education-content">
			<h2 class="display-5 fs-2">Education</h2>
		</button>
		<div class="collapse" id="ac-education-content">
			<div class="row mt-2" id="ac-ec-row">
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">Associate of Arts - AA Degree, Computer Engineering</h5>
				<h6 class="card-subtitle mb-2 text-muted">Tallahassee State College, Tallahassee, FL</h6>
				<p class="card-text">August 2023 - July 2025</p>
				</div>
			</div>
			<div class="card col-xl-3 m-2">
				<div class="card-body">
				<h5 class="card-title">* Bachelor of Science in Electrical Engineering</h5>
				<h6 class="card-subtitle mb-2 text-muted">Mapúa University, Manila, PH</h6>
				<p class="card-text">August 2020 - February 2022</p>
				<p class="card-text">* Degree has not been obtained due to migration to the United States.</p>
				</div>
			</div>
			</div> <!-- ac-ec-row -->
		</div> <!-- ac-education-content -->
		</div> <!-- ac-education -->
	</article> <!-- ac-body -->
`;

let projects_content = `
	<div class="container-fluid" id="pc-title">
		<h1 class="display-3">Projects</h1>
	</div>
	<article id="pc-body">
		<div class="container-fluid" id="pc-row-container">
		<div class="row mt-2" id="pc-row">
			<a class="btn btn-outline-dark col-xl-3 m-2" href="https://github.com/ederevx/ederevx.github.io" target="_blank">
			<div class="card-body text-start">
				<h5 class="card-title">ederevx.github.io</h5>
				<h6 class="card-subtitle mb-2">Website</h6>
				<p class="card-text">Languages: HTML, CSS</p>
			</div>
			</a>
			<a class="btn btn-outline-dark col-xl-3 m-2" href="https://github.com/ederevx/android_scripts" target="_blank">
			<div class="card-body text-start">
				<h5 class="card-title">Android Build Scripts for OnePlus 5T</h5>
				<h6 class="card-subtitle mb-2">Linux Bash Script</h6>
				<p class="card-text">Languages: Bash/Shell</p>
			</div>
			</a>
			<a class="btn btn-outline-dark col-xl-3 m-2" href="https://github.com/ederevx/x-ft_kernel_oneplus_msm8998" target="_blank">
			<div class="card-body text-start">
				<h5 class="card-title">X-FT Kernel for OnePlus 5T</h5>
				<h6 class="card-subtitle mb-2">Linux Kernel, Version 4.14</h6>
				<p class="card-text">Languages: C, Makefile, Shell</p>
			</div>
			</a>
			<a class="btn btn-outline-dark col-xl-3 m-2" href="https://github.com/ederevx/x_kernel_oneplus_msm8998" target="_blank">
			<div class="card-body text-start">
				<h5 class="card-title">X Kernel for OnePlus 5T</h5>
				<h6 class="card-subtitle mb-2">Linux Kernel, Version 4.4</h6>
				<p class="card-text">Languages: C, Makefile, Shell</p>
			</div>
			</a>
		</div> <!-- pc-row -->
		</div> <!-- pc-row-container -->
	</article> <!-- pc-body -->
`;

let contact_content = `
	<div class="container-fluid" id="cc-title">
		<h1 class="display-3">Contact</h1>
	</div>
	<article id="cc-body">
		<div class="container-fluid" id="cc-row-container">
		<div class="row mt-2" id="cc-row">
			<a class="btn btn-outline-dark col-md-3 m-2" href="https://github.com/ederevx" target="_blank">
			<h2 class="display-5 fs-2">Github</h2>
			</a>
			<a class="btn btn-outline-dark col-md-3 m-2" href="https://www.linkedin.com/in/ederevx" target="_blank">
			<h2 class="display-5 fs-2">LinkedIn</h2>
			</a>
			<a class="btn btn-outline-dark col-md-3 m-2" href="mailto:evcsinsuan@gmail.com" target="_blank">
			<h2 class="display-5 fs-2">Email</h2>
			</a>
		</div> <!-- cc-row -->
		</div> <!-- cc-row-container -->
	</article> <!-- cc-body -->
`;
