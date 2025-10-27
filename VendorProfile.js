import React, { useState, useEffect } from "react";
import axios from 'axios';
import { Link } from 'react-router-dom';
import Navbar from "../components/navbar";
import './vendorWebsite.css';
import EnquiryModal from './Enquiry';

const VendorProfile = () => {
  const [vendorData, setVendorData] = useState(null);
  const [error, setError] = useState(null);
  const [products, setProducts] = useState([]);
  const [about, setAbout] = useState([]);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [userData, setUserData] = useState(null);
  const [activeTab, setActiveTab] = useState('products'); // New state for tab management

  useEffect(() => {
    const slug = window.location.pathname.split("/")[1]; 
    const vendortoken = window.localStorage.getItem("vendortoken");
    
    if (vendortoken) {
      fetch(`${process.env.REACT_APP_API_URL}/vendorData`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({ vendortoken }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "ok") {
            setUserData(data.data);
          } else {
            setError(data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          setError(error.message);
        });
    }
    
    const fetchVendorData = async () => {
      try {
        const response = await axios.get(`${process.env.REACT_APP_API_URL}/getBusinessSlug/${slug}`);
        if (response.data.status === 'ok') {
          setVendorData(response.data.data);
        } else {
          setError(response.data.message);
        }
      } catch (error) {
        console.error('Error fetching vendor data:', error);
        setError(error.message);
      }
    };

    fetchVendorData();
  }, []);

  useEffect(() => {
    const fetchProducts = async () => {
      if (vendorData) {
        try {
          const response = await axios.post(`${process.env.REACT_APP_API_URL}/getVendorThreeProduct`, { vendorId: vendorData._id });

          if (response.data.status === 'ok') {
            setProducts(response.data.data);
          } else {
            console.error('Error fetching products:', response.data.message);
          }
        } catch (error) {
          console.error('Error fetching products:', error);
        }
      }
    };
    
    const fetchWebsiteDetails = async () => {
      if (vendorData) {
        try {
          const response = await axios.post(`${process.env.REACT_APP_API_URL}/getWebsiteDetailsVendor`, { vendorId: vendorData._id });

          if (response.data.status === 'ok') {
            setAbout(response.data.data);
          } else {
            console.error('Error fetching website details:', response.data.message);
          }
        } catch (error) {
          console.error('Error fetching website details:', error);
        }
      }
    };

    fetchProducts();
    fetchWebsiteDetails();
  }, [vendorData]);

  if (error) {
    return <div>Error: {error}</div>;
  }

  if (!vendorData) {
    return <div>Loading...</div>;
  }

  const handleEnquiryClick = (product) => {
    setSelectedProduct(product);
    setShowModal(true);
  };

  const handleModalClose = () => {
    setShowModal(false);
    setSelectedProduct(null);
  };

  const renderSellerDetails = () => (
    <div className="seller-details-content">
      {/* Company Overview */}
      <div className="details-section">
        <h3 className="section-title">Company Overview</h3>
        <div className="details-grid">
          <div className="detail-item">
            <span className="detail-label">Company Name:</span>
            <span className="detail-value">{vendorData.businessName || 'N/A'}</span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Business Type:</span>
            <span className="detail-value">
              {vendorData.businessDetails && vendorData.businessDetails.length > 0 
                ? vendorData.businessDetails.join(', ') 
                : 'N/A'}
            </span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Ownership Type:</span>
            <span className="detail-value">{vendorData.Ownership || 'N/A'}</span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Year of Establishment:</span>
            <span className="detail-value">{vendorData.establishment || 'N/A'}</span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Number of Employees:</span>
            <span className="detail-value">{vendorData.NoEmployee || 'N/A'}</span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Annual Turnover:</span>
            <span className="detail-value">{vendorData.AnnualTakeover || 'N/A'}</span>
          </div>
        </div>
      </div>

      {/* Contact Information */}
      <div className="details-section">
        <h3 className="section-title">Contact Information</h3>
        <div className="details-grid">
          <div className="detail-item">
            <span className="detail-label">Phone Number:</span>
            <span className="detail-value">
              <Link to={`tel:${vendorData.number}`} className="contact-link">
                <i className="fas fa-phone"></i> {vendorData.number || 'N/A'}
              </Link>
            </span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Office Contact:</span>
            <span className="detail-value">{vendorData.OfficeContact || 'N/A'}</span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Fax Number:</span>
            <span className="detail-value">{vendorData.FaxNumber || 'N/A'}</span>
          </div>
          
          <div className="detail-item">
            <span className="detail-label">Email:</span>
            <span className="detail-value">{vendorData.email || 'N/A'}</span>
          </div>
        </div>
      </div>

      {/* Address Information */}
      <div className="details-section">
        <h3 className="section-title">Address</h3>
        <div className="address-content">
          <div className="address-item">
            <i className="fas fa-map-marker-alt"></i>
            <div className="address-text">
              <p>{vendorData.Address || 'N/A'}</p>
              <p>{vendorData.City}, {vendorData.State} {vendorData.Pincode}</p>
              <p>{vendorData.Country}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Company Description */}
      {vendorData.companyDescription && (
        <div className="details-section">
          <h3 className="section-title">About Company</h3>
          <div className="company-description">
            <p>{vendorData.companyDescription}</p>
          </div>
        </div>
      )}

      {/* Website Details */}
      {about && about.length > 0 && (
        <div className="details-section">
          <h3 className="section-title">Additional Information</h3>
          <div className="additional-info">
            {about.map((item, index) => (
              <div key={index} className="info-item">
                <h4>{item.title}</h4>
                <p>{item.description}</p>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );

  const renderProductsServices = () => (
    <div className="products-services-content">
      <div className="container">
        <div className="row">
          {products.length > 0 ? (
            products.map((product) => (
              <div key={product._id} className="col-md-4 mb-4">
                <div className="card h-100 product-card">
                  {product.image ? (
                    <img 
                      src={`${process.env.REACT_APP_API_URL}/${product.image[0].replace('\\', '/')}`} 
                      className="card-img-top homeproductimage" 
                      alt={product.name}
                    />
                  ) : (
                    <img 
                      src="path_to_default_image.jpg" 
                      className="card-img-top" 
                      alt="default"
                    />
                  )}
                  <div className="card-body">
                    <h5 className="card-title ellipsis2">{product.name}</h5>
                    <p className="card-text product-description">{product.description}</p>
                    <div className="product-actions">
                      <button 
                        type="button" 
                        className="submit-btn" 
                        onClick={() => handleEnquiryClick(product)}
                      >
                        <i className="fa fa-send-o"></i> Ask For Details
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            ))
          ) : (
            <div className="col-12 text-center">
              <div className="no-products">
                <i className="fas fa-box-open"></i>
                <h3>No Products Available</h3>
                <p>This vendor hasn't added any products yet.</p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );

  return (
    <div>
      <Navbar/>
      <div className="vendorwebsite">
        {/* Header Section */}
        <div className="vendor-header">
          <div className="container">
            <div className="row align-items-center">
              <div className="col-lg-8 col-md-7">
                <div className="vendor-info">
                  {vendorData.logo && (
                    <div className="vendor-logo">
                      <img src={vendorData.logo} alt={vendorData.businessName} />
                    </div>
                  )}
                  <div className="vendor-details">
                    <h1 className="vendor-name">{vendorData.businessName}</h1>
                    <div className="locationwebsite">
                      <i className='fas fa-map-marker-alt'></i> 
                      {vendorData.City}, {vendorData.State}
                    </div>
                    <div className="vendor-meta">
                      {vendorData.establishment && (
                        <span className="establishment">
                          <i className="fas fa-calendar-alt"></i> 
                          Since {vendorData.establishment}
                        </span>
                      )}
                      {vendorData.businessDetails && vendorData.businessDetails.length > 0 && (
                        <span className="business-type">
                          <i className="fas fa-briefcase"></i> 
                          {vendorData.businessDetails[0]}
                        </span>
                      )}
                    </div>
                  </div>
                </div>
              </div>
              <div className="col-lg-4 col-md-5 text-right">
                <div className="contact-actions">
                  <Link to={`tel:${vendorData.number}`} className="phone-link">
                    <i className='fas fa-phone'></i> {vendorData.number}
                  </Link>
                  <button className="contact-btn" onClick={() => setShowModal(true)}>
                    <i className="fas fa-envelope"></i> Send Enquiry
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Tab Navigation */}
        <div className="tab-navigation">
          <div className="container">
            <div className="nav-tabs-custom">
              <button 
                className={`tab-btn ${activeTab === 'products' ? 'active' : ''}`}
                onClick={() => setActiveTab('products')}
              >
                <i className="fas fa-boxes"></i> Products & Services
              </button>
              <button 
                className={`tab-btn ${activeTab === 'seller' ? 'active' : ''}`}
                onClick={() => setActiveTab('seller')}
              >
                <i className="fas fa-building"></i> Seller Details
              </button>
            </div>
          </div>
        </div>

        {/* Tab Content */}
        <div className="tab-content">
          <div className="container">
            {activeTab === 'products' && renderProductsServices()}
            {activeTab === 'seller' && renderSellerDetails()}
          </div>
        </div>

        {/* Enquiry Modal */}
        <EnquiryModal 
          show={showModal} 
          handleClose={handleModalClose} 
          product={selectedProduct} 
          userData={userData}
          vendorData={vendorData}
        />

        {/* Footer Section */}
        <div className="footer-section">
          <div className="getTouch">
            Get in touch with us
          </div>
        </div>
      </div>
    </div>
  );
};

export default VendorProfile;