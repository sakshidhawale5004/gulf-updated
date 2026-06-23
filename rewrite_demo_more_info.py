import re

with open('book-a-demo.html', 'r', encoding='utf-8') as f:
    content = f.read()

header_end = content.find('</header>') + len('</header>')
footer_start = content.find('<footer')

if header_end == -1 or footer_start == -1:
    print('Error finding header/footer')
    exit(1)

header = content[:header_end]
footer = content[footer_start:]

main_content = '''
<style>
    .premium-page-wrapper {
        background-color: #f8f9fa;
        min-height: calc(100vh - 200px);
    }
    .info-section {
        background-color: white;
        padding: 60px 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        margin-top: 40px;
        margin-bottom: 40px;
    }
    .feature-icon {
        color: var(--primary-orange);
        font-size: 2.5rem;
        margin-bottom: 20px;
    }
    .demo-heading {
        color: var(--primary-green);
        font-weight: 800;
        margin-bottom: 20px;
    }
    .section-title {
        color: var(--primary-green);
        font-weight: 700;
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }
    .section-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background-color: var(--primary-orange);
    }
    .text-center .section-title::after {
        left: 50%;
        transform: translateX(-50%);
    }
</style>
<main class="premium-page-wrapper py-5">
    <div class="container">
        <!-- Short Info (Hero Summary) -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10 text-center">
                <h1 class="display-4 demo-heading">See GulfTP in Action</h1>
                <p class="lead text-muted px-md-5">GulfTP is the definitive transfer pricing platform for the GCC. We provide accurate, localized benchmarking data alongside powerful analytical tools, empowering professionals to stay compliant and make data-driven decisions faster.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="info-section">
                    
                    <!-- More Info: Core Offerings -->
                    <h3 class="section-title text-center">Everything You Need in One Platform</h3>
                    <div class="row g-4 text-start mb-5">
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

                    <!-- More Info: Why Choose GulfTP -->
                    <div class="mt-5 pt-4 border-top">
                        <h3 class="section-title">Why Choose GulfTP?</h3>
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="d-flex mb-3">
                                        <i class="fa-solid fa-check text-success me-3 mt-1"></i>
                                        <span class="text-muted"><strong>Built for the GCC:</strong> Specialized localized data you won't find on global generic databases.</span>
                                    </li>
                                    <li class="d-flex mb-3">
                                        <i class="fa-solid fa-check text-success me-3 mt-1"></i>
                                        <span class="text-muted"><strong>Time-Saving Tools:</strong> Includes proprietary tools for DEMPE analysis and credit rating estimation.</span>
                                    </li>
                                    <li class="d-flex mb-3">
                                        <i class="fa-solid fa-check text-success me-3 mt-1"></i>
                                        <span class="text-muted"><strong>Trusted by Experts:</strong> Relied upon by over 100 transfer pricing professionals across the Middle East.</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-center">
                                <img src="Why_GulfTP_Services_Database_GulfTP-768x614.webp" alt="GulfTP Platform" class="img-fluid rounded-4 shadow-sm" style="max-height: 250px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to Action -->
                    <div class="bg-light p-5 rounded-4 mt-5 text-start border position-relative overflow-hidden">
                        <div class="position-absolute top-0 end-0 h-100 w-50 d-none d-md-block" style="background: linear-gradient(90deg, transparent, rgba(243, 146, 35, 0.05));"></div>
                        <div class="row align-items-center position-relative z-1">
                            <div class="col-md-8">
                                <h3 class="fw-bold text-dark mb-3">Schedule Your Personalized Demo</h3>
                                <p class="text-muted fs-5 mb-0">Let our experts show you exactly how GulfTP can streamline your workflow and secure your compliance.</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                                <a href="contact.html" class="btn btn-lg w-100 fw-bold text-white shadow-sm" style="background-color: var(--primary-orange); border-radius: 12px; padding: 15px 30px; transition: transform 0.2s;">Request Demo <i class="fa-solid fa-arrow-right ms-2"></i></a>
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

print('Updated book-a-demo.html with more comprehensive info.')
