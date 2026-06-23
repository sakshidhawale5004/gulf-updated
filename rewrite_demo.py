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
        margin-top: 50px;
        margin-bottom: 50px;
    }
    .feature-icon {
        color: var(--primary-orange);
        font-size: 2.5rem;
        margin-bottom: 20px;
    }
    .demo-heading {
        color: var(--primary-green);
        font-weight: 800;
        margin-bottom: 30px;
    }
</style>
<main class="premium-page-wrapper py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="info-section text-center">
                    <h1 class="display-4 demo-heading">Experience GulfTP in Action</h1>
                    <p class="lead text-muted mb-5">Discover how our comprehensive transfer pricing databases and cutting-edge analytical tools can transform your benchmarking process.</p>
                    
                    <div class="row g-4 text-start mb-5">
                        <div class="col-md-4">
                            <div class="p-4 border rounded-3 h-100 bg-light">
                                <i class="fa-solid fa-database feature-icon"></i>
                                <h4 class="fw-bold text-dark mb-3">Comprehensive Data</h4>
                                <p class="text-muted">Access the most reliable and extensive Transfer Pricing data across the GCC, covering companies, interest rates, services, and IP licensing.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 border rounded-3 h-100 bg-light">
                                <i class="fa-solid fa-chart-line feature-icon"></i>
                                <h4 class="fw-bold text-dark mb-3">Advanced Analytics</h4>
                                <p class="text-muted">Utilize our proprietary tools for DEMPE analysis and credit rating estimation to strengthen your compliance documentation.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 border rounded-3 h-100 bg-light">
                                <i class="fa-solid fa-bolt feature-icon"></i>
                                <h4 class="fw-bold text-dark mb-3">Seamless Workflow</h4>
                                <p class="text-muted">Experience an intuitive platform designed specifically to streamline your transfer pricing searches and save you valuable time.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-light p-5 rounded-4 mt-5 text-start border">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="fw-bold text-dark mb-3">Ready to see it live?</h3>
                                <p class="text-muted fs-5 mb-0">Get in touch with our team to schedule a personalized walkthrough of the GulfTP platform tailored to your specific benchmarking needs.</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-4 mt-md-0">
                                <a href="contact.html" class="btn btn-lg w-100 fw-bold text-white" style="background-color: var(--primary-orange); border-radius: 12px; padding: 15px 30px;">Contact Us <i class="fa-solid fa-arrow-right ms-2"></i></a>
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

print('Updated book-a-demo.html successfully')
