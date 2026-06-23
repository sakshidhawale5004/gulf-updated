import re

with open('book-an-appointment.html', 'r', encoding='utf-8') as f:
    appt_content = f.read()

# Extract the form panel from book-an-appointment.html
# Start from <div class="col-lg-7 bg-white p-5"> until </div> just before closing the row.
form_match = re.search(r'(<!-- Right Form Panel -->\s*<div class="col-lg-7 bg-white p-5">.*?</form>\s*</div>)', appt_content, flags=re.DOTALL)
if not form_match:
    print("Could not find form in book-an-appointment.html")
    exit(1)
form_html = form_match.group(1)

# Modify the form HTML slightly to fit a Demo
form_html = form_html.replace('Your Details', 'Request a Demo')
form_html = form_html.replace('Confirm Appointment', 'Submit Request')


with open('book-a-demo.html', 'r', encoding='utf-8') as f:
    demo_content = f.read()

header_end = demo_content.find('</header>') + len('</header>')
footer_start = demo_content.find('<footer')

header = demo_content[:header_end]
footer = demo_content[footer_start:]

main_content = f'''
<style>
    .premium-page-wrapper {{
        background-color: #f8f9fa;
        min-height: calc(100vh - 200px);
    }}
    .premium-card-shadow {{
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }}
    .premium-left-panel {{
        background: linear-gradient(135deg, #08664b, #054834);
        overflow: hidden;
    }}
    .panel-bg-overlay {{
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
        transform: rotate(30deg);
        pointer-events: none;
    }}
    .premium-input {{
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }}
    .premium-input:focus {{
        background-color: #fff;
        border-color: #08664b;
        box-shadow: 0 0 0 0.25rem rgba(8, 102, 75, 0.1);
    }}
    .premium-submit-btn {{
        transition: all 0.3s ease;
    }}
    .premium-submit-btn:hover {{
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(232, 97, 44, 0.3) !important;
    }}
    
    .feature-icon {{
        color: var(--primary-orange);
        font-size: 2.5rem;
        margin-bottom: 20px;
    }}
    .demo-heading {{
        color: var(--primary-green);
        font-weight: 800;
        margin-bottom: 20px;
    }}
    .section-title {{
        color: var(--primary-green);
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }}
    .section-title::after {{
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background-color: var(--primary-orange);
    }}
    .text-center .section-title::after {{
        left: 50%;
        transform: translateX(-50%);
    }}
</style>
<main class="premium-page-wrapper py-5">
    <div class="container">
        
        <!-- Top Section: Form + Short Info -->
        <div class="row g-0 premium-card-shadow rounded-4 overflow-hidden mb-5">
            <!-- Left Info Panel -->
            <div class="col-lg-5 premium-left-panel p-5 text-white d-flex flex-column justify-content-center position-relative">
                <div class="panel-bg-overlay"></div>
                <div class="position-relative z-index-1">
                    <span class="badge bg-white text-success mb-3 px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">SEE IT LIVE</span>
                    <h1 class="display-5 fw-bold mb-4">Book A Demo</h1>
                    <p class="fs-5 text-white-50 mb-4">GulfTP is the definitive transfer pricing platform for the GCC. Schedule a live demo to see how our databases and tools can streamline your transfer pricing benchmarking and compliance workflows.</p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Built for the GCC</h6>
                            <small class="text-white-50">Localized data you won't find anywhere else</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Advanced Analytics</h6>
                            <small class="text-white-50">Includes DEMPE & Credit Rating tools</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Trusted by Experts</h6>
                            <small class="text-white-50">Relied upon by 100+ professionals</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Form Panel -->
            {form_html}
        </div>
        
        <!-- Bottom Section: More Info (Core Offerings) -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-12">
                <div class="bg-white p-5 rounded-4 premium-card-shadow">
                    <h3 class="section-title text-center">Everything You Need in One Platform</h3>
                    <div class="row g-4 text-start mt-2">
                        <div class="col-md-6">
                            <div class="d-flex p-4 border rounded-3 h-100 bg-light align-items-start">
                                <i class="fa-solid fa-building feature-icon me-4 mt-1" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="fw-bold text-dark mb-2">Gulf Company Database</h5>
                                    <p class="text-muted mb-0">Search through verified financials and data of Middle Eastern companies. Filter by NAICS/ISIC codes, revenue, and more to find precise comparables.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex p-4 border rounded-3 h-100 bg-light align-items-start">
                                <i class="fa-solid fa-chart-line feature-icon me-4 mt-1" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="fw-bold text-dark mb-2">Interest Rates Database</h5>
                                    <p class="text-muted mb-0">Benchmark intercompany loans accurately with access to an extensive repository of loan agreements and current interest rate data in the GCC.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex p-4 border rounded-3 h-100 bg-light align-items-start">
                                <i class="fa-solid fa-server feature-icon me-4 mt-1" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="fw-bold text-dark mb-2">Services Database</h5>
                                    <p class="text-muted mb-0">Evaluate management fees and intra-group services with confidence using our detailed records of third-party service agreements.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex p-4 border rounded-3 h-100 bg-light align-items-start">
                                <i class="fa-solid fa-file-contract feature-icon me-4 mt-1" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="fw-bold text-dark mb-2">IP Licensing Database</h5>
                                    <p class="text-muted mb-0">Analyze royalty rates and IP transactions with comprehensive intellectual property license agreements sourced specifically for the region.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
'''

new_html = header + main_content + footer

with open('book-a-demo.html', 'w', encoding='utf-8') as f:
    f.write(new_html)

print('Updated book-a-demo.html to combine form and info successfully.')
