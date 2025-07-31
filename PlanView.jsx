import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import Navbar from '../components/navbar';
import './planview.css';

const PlanView = () => {
  const { id } = useParams();
  const [plan, setPlan] = useState(null);
  const [allPlans, setAllPlans] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  // TradeIndia style plans data
  const tradeIndiaPlans = [
    {
      id: 'verified-seller',
      planName: 'TradeIndia Verified Seller',
      planType: 'Basic Package',
      planPrice: '0',
      planList: [
        'Verified Seller Badge',
        'Basic Product Listing',
        'Contact Information Display',
        'Email Support',
        'Mobile App Access',
        'Basic Analytics'
      ],
      isPopular: false,
      color: '#6c757d'
    },
    {
      id: 'sme-biz-connect',
      planName: 'SME Biz Connect Package',
      planType: 'Professional Package',
      planPrice: '15000',
      planList: [
        'Enhanced Product Showcase',
        'Priority Listing',
        'Lead Generation Tools',
        'Advanced Analytics',
        'Phone Support',
        'Social Media Integration',
        'Custom Brochure Design',
        'Export Documentation Support'
      ],
      isPopular: false,
      color: '#17a2b8'
    },
    {
      id: 'sme-biz-plus',
      planName: 'SME Biz Plus Package',
      planType: 'Premium Package',
      planPrice: '35000',
      planList: [
        'Premium Product Placement',
        'Featured Seller Status',
        'Advanced Lead Management',
        'Multi-language Support',
        'Dedicated Account Manager',
        'Custom Website Integration',
        'Trade Show Participation',
        'International Market Access',
        'Priority Customer Support',
        'Advanced SEO Optimization'
      ],
      isPopular: true,
      color: '#28a745'
    },
    {
      id: 'sme-biz-pro',
      planName: 'SME Biz PRO Package',
      planType: 'Enterprise Package',
      planPrice: '75000',
      planList: [
        'Enterprise-level Features',
        'Top Banner Placement',
        'Unlimited Product Listings',
        'AI-powered Lead Matching',
        'White-label Solutions',
        '24/7 Premium Support',
        'Custom API Integration',
        'Advanced Market Intelligence',
        'Personal Brand Manager',
        'Global Trade Assistance',
        'Custom Mobile App',
        'Advanced Analytics Dashboard'
      ],
      isPopular: false,
      color: '#dc3545'
    }
  ];

  useEffect(() => {
    const fetchPlanData = async () => {
      try {
        setLoading(true);
        
        // If ID is provided, fetch specific plan
        if (id) {
          const response = await axios.get(`${process.env.REACT_APP_API_URL}/getPlanById/${id}`);
          if (response.data && response.data._id) {
            setPlan(response.data);
          } else {
            // If API plan not found, check TradeIndia plans
            const tradeIndiaPlan = tradeIndiaPlans.find(p => p.id === id);
            if (tradeIndiaPlan) {
              setPlan(tradeIndiaPlan);
            } else {
              setError('Plan not found.');
            }
          }
        }
        
        // Always fetch all plans for comparison
        try {
          const allPlansResponse = await axios.get(`${process.env.REACT_APP_API_URL}/getAllPlans`);
          setAllPlans(allPlansResponse.data || []);
        } catch (apiError) {
          // Use TradeIndia plans as fallback
          setAllPlans(tradeIndiaPlans);
        }
        
      } catch (error) {
        console.error('Error fetching plan:', error);
        setError('Something went wrong!');
      } finally {
        setLoading(false);
      }
    };

    fetchPlanData();
  }, [id]);

  if (loading) {
    return (
      <div>
        <Navbar />
        <div className="loading text-center mt-5">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
          <p className="mt-3">Loading plan details...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div>
        <Navbar />
        <div className="error-container">
          <i className="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
          <h3>{error}</h3>
          <p className="text-muted mb-4">We couldn't find the plan you're looking for.</p>
          <Link to="/plans" className="btn btn-primary">
            <i className="fas fa-arrow-left me-2"></i>
            View All Plans
          </Link>
        </div>
      </div>
    );
  }

  // If specific plan is selected, show detailed view
  if (plan && id) {
    return (
      <>
        <Navbar />

        {/* Banner Section */}
        <section className="planview-banner">
          <div className="container text-center">
            <div className="banner-content">
              <h1>{plan.planName}</h1>
              <p className="text-muted">{plan.planType}</p>
              {plan.isPopular && (
                <span className="badge bg-warning text-dark fs-6 mt-2">
                  <i className="fas fa-star me-1"></i>
                  Most Popular
                </span>
              )}
            </div>
          </div>
        </section>

        {/* Plan Details */}
        <section className="planview-details">
          <div className="container">
            <div className="plan-card shadow">
              <div className="plan-header">
                <div className="plan-title-section">
                  <h2>{plan.planName}</h2>
                  <p className="plan-subtitle text-muted">{plan.planType}</p>
                </div>
                <div className="plan-price-section">
                  <span className="plan-price">
                    {plan.planPrice === 'Free' || plan.planPrice === '0' ? 
                      'FREE' : 
                      `₹${parseInt(plan.planPrice).toLocaleString('en-IN')}`
                    }
                  </span>
                  <p className="price-period text-muted">per year</p>
                </div>
              </div>

              <div className="plan-features">
                <h4>What's Included:</h4>
                {plan.planList && plan.planList.length > 0 ? (
                  <ul>
                    {plan.planList.map((item, idx) => (
                      <li key={idx}>
                        <i className="fas fa-check-circle text-success me-3"></i>
                        <span>{item}</span>
                      </li>
                    ))}
                  </ul>
                ) : (
                  <div className="no-features">
                    <i className="fas fa-info-circle text-info me-2"></i>
                    <span className="text-muted">No additional features listed for this plan.</span>
                  </div>
                )}
              </div>

              <div className="plan-actions text-center mt-4">
                <Link to="/plans" className="btn btn-secondary me-3">
                  <i className="fas fa-arrow-left me-2"></i>
                  Back to Plans
                </Link>
                <button className="btn btn-primary">
                  <i className="fas fa-shopping-cart me-2"></i>
                  Choose This Plan
                </button>
              </div>
            </div>

            {/* Related Plans Section */}
            {allPlans.length > 1 && (
              <div className="related-plans mt-5">
                <h3 className="text-center mb-4">Compare Other Plans</h3>
                <div className="tradeindia-packages">
                  {allPlans
                    .filter(p => p.id !== plan.id && p._id !== plan._id)
                    .slice(0, 3)
                    .map((relatedPlan, index) => (
                      <div 
                        key={relatedPlan.id || relatedPlan._id} 
                        className={`package-card ${relatedPlan.isPopular ? 'featured' : ''}`}
                      >
                        {relatedPlan.isPopular && (
                          <div className="popular-badge">
                            <i className="fas fa-crown me-1"></i>
                            Popular
                          </div>
                        )}
                        <div className="package-title">{relatedPlan.planName}</div>
                        <div className="package-price">
                          {relatedPlan.planPrice === 'Free' || relatedPlan.planPrice === '0' ? 
                            'FREE' : 
                            `₹${parseInt(relatedPlan.planPrice).toLocaleString('en-IN')}`
                          }
                        </div>
                        <ul className="package-features">
                          {(relatedPlan.planList || []).slice(0, 4).map((feature, idx) => (
                            <li key={idx}>
                              <i className="fas fa-check text-success me-2"></i>
                              {feature}
                            </li>
                          ))}
                          {(relatedPlan.planList || []).length > 4 && (
                            <li className="text-muted">
                              +{(relatedPlan.planList || []).length - 4} more features
                            </li>
                          )}
                        </ul>
                        <Link 
                          to={`/plan/${relatedPlan.id || relatedPlan._id}`}
                          className="btn btn-outline-primary w-100"
                        >
                          View Details
                        </Link>
                      </div>
                    ))}
                </div>
              </div>
            )}
          </div>
        </section>
      </>
    );
  }

  // If no specific plan ID, show all plans (TradeIndia style)
  return (
    <>
      <Navbar />
      
      {/* Banner Section */}
      <section className="planview-banner">
        <div className="container text-center">
          <h1>Choose Your Business Plan</h1>
          <p>Grow your business with TradeIndia's comprehensive packages</p>
        </div>
      </section>

      {/* All Plans Display */}
      <section className="planview-details">
        <div className="container">
          <div className="tradeindia-packages">
            {(allPlans.length > 0 ? allPlans : tradeIndiaPlans).map((planItem, index) => (
              <div 
                key={planItem.id || planItem._id} 
                className={`package-card ${planItem.isPopular ? 'featured' : ''}`}
                style={planItem.color ? {borderTopColor: planItem.color} : {}}
              >
                {planItem.isPopular && (
                  <div className="popular-badge">
                    <i className="fas fa-crown me-1"></i>
                    Most Popular
                  </div>
                )}
                
                <div className="package-header">
                  <div className="package-title">{planItem.planName}</div>
                  <div className="package-subtitle text-muted">{planItem.planType}</div>
                </div>
                
                <div className="package-price">
                  {planItem.planPrice === 'Free' || planItem.planPrice === '0' ? 
                    'FREE' : 
                    `₹${parseInt(planItem.planPrice).toLocaleString('en-IN')}`
                  }
                  <span className="price-period">/year</span>
                </div>
                
                <ul className="package-features">
                  {(planItem.planList || []).map((feature, idx) => (
                    <li key={idx}>
                      <i className="fas fa-check text-success me-2"></i>
                      {feature}
                    </li>
                  ))}
                </ul>
                
                <div className="package-actions">
                  <Link 
                    to={`/plan/${planItem.id || planItem._id}`}
                    className="btn btn-outline-primary w-100 mb-2"
                  >
                    <i className="fas fa-eye me-2"></i>
                    View Details
                  </Link>
                  <button className="btn btn-primary w-100">
                    <i className="fas fa-shopping-cart me-2"></i>
                    Choose Plan
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
};

export default PlanView;