import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link, useParams, useNavigate } from 'react-router-dom';
import VendorHeader from './vendorHeader';

import './businessProfile.css';

import VendorSidebar from './VendorSidebar';
import Navbar from '../components/navbar';

const UpdateProfileVendor = () => {
  const { vendorId } = useParams();
  const navigate = useNavigate();
  const [vendorData, setVendorData] = useState({
    websiteDisplayName: '',
    mainProductKeywords: '',
    companyDescription: '',
    businessName: '',
    OfficeContact: '',
    FaxNumber: '',
    Ownership: '',
    AnnualTakeover: '',
    establishment: '',
    NoEmployee: '',
    selectType: '',
    Address: '',
    City: '',
    State: '',
    Country: '',
    Pincode: ''
  });
  const [error, setError] = useState(null);
  const [activeSubMenu, setActiveSubMenu] = useState(null);
  const [businessType, setBusinessType] = useState(null);
  const [profileCompleteness, setProfileCompleteness] = useState(0);
  const [showCompanyDetails, setShowCompanyDetails] = useState(true);
  const [showAddressDetails, setShowAddressDetails] = useState(false);
  const [activeSection, setActiveSection] = useState('basic');

  const toggleCompanyDetails = () => setShowCompanyDetails(!showCompanyDetails);
  const toggleAddressDetails = () => setShowAddressDetails(!showAddressDetails);

  useEffect(() => {
    const handleScroll = () => {
      const basic = document.getElementById('basic-section');
      const business = document.getElementById('business-section');
      const address = document.getElementById('address-section');
      const scrollPosition = window.scrollY + 250;

      if (address && address.offsetTop <= scrollPosition) {
        setActiveSection('address');
      } else if (business && business.offsetTop <= scrollPosition) {
        setActiveSection('business');
      } else if (basic && basic.offsetTop <= scrollPosition) {
        setActiveSection('basic');
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    const vendortoken = window.localStorage.getItem('vendortoken');

    if (!vendortoken) {
      setError('No token found');
      return;
    }

    axios.post(`${process.env.REACT_APP_API_URL}/vendorData`, { vendortoken })
      .then(response => {
        if (response.data.status === 'ok') {
          setVendorData(response.data.data);
          setBusinessType(response.data.data.businessType);
        } else {
          setError(response.data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        setError(error.message);
      });
  }, [vendorId]);

  const calculateCompleteness = (data) => {
    let filledFields = 0;
    const totalFields = 16;

    if (data.websiteDisplayName && data.websiteDisplayName.trim() !== '') filledFields++;
    if (data.mainProductKeywords && data.mainProductKeywords.trim() !== '') filledFields++;
    if (data.companyDescription && data.companyDescription.trim() !== '') filledFields++;
    if (data.businessName && data.businessName.trim() !== '') filledFields++;
    if (data.OfficeContact && data.OfficeContact.trim() !== '') filledFields++;
    if (data.FaxNumber && data.FaxNumber.trim() !== '') filledFields++;
    if (data.Ownership && data.Ownership.trim() !== '') filledFields++;
    if (data.AnnualTakeover && data.AnnualTakeover.trim() !== '') filledFields++;
    if (data.establishment && data.establishment.trim() !== '') filledFields++;
    if (data.NoEmployee && data.NoEmployee.trim() !== '') filledFields++;
    if (data.selectType && data.selectType.trim() !== '') filledFields++;
    if (data.Address && data.Address.trim() !== '') filledFields++;
    if (data.City && data.City.trim() !== '') filledFields++;
    if (data.State && data.State.trim() !== '') filledFields++;
    if (data.Country && data.Country.trim() !== '') filledFields++;
    if (data.Pincode && data.Pincode.trim() !== '') filledFields++;

    const completeness = Math.round((filledFields / totalFields) * 100);
    setProfileCompleteness(completeness);
  };

  useEffect(() => {
    calculateCompleteness(vendorData);
  }, [vendorData]);

  const handleSubMenuToggle = (index) => {
    setActiveSubMenu(activeSubMenu === index ? null : index);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setVendorData((prevData) => ({
      ...prevData,
      [name]: value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const vendortoken = window.localStorage.getItem('vendortoken');
    
    axios.put(`${process.env.REACT_APP_API_URL}/BusinessProfile`, vendorData, {
      headers: { 'Authorization': `Bearer ${vendortoken}` }
    })
    .then(response => {
      if (response.data.status === 'ok') {
        navigate('/Vendor/Dashboard');
      } else {
        setError(response.data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      setError(error.message);
    });
  };

  return (
    <div className="">
      <Navbar />
      <div className='main-container'>
        <VendorSidebar />
        <form onSubmit={handleSubmit} className='formbusiness'>
          <div className='info-layout'>
            {error && <p className="error">{error}</p>}
            <div className='business-container-box'>
              
              {/* Basic Company Information Section */}
              <div className='userinfo-container' id="basic-section">
                <div className='basic-content'>
                  <h3>Basic Company Information</h3>
                </div>

                <div className="form-container1">
                  {/* Website Display Name */}
                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="websiteDisplayName">
                        Website Display Name <span className="info-icon">ⓘ</span>
                      </label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="websiteDisplayName"
                      name="websiteDisplayName"
                      placeholder="onlineshop"
                      value={vendorData.websiteDisplayName}
                      onChange={handleChange}
                    />
                  </div>

                  {/* Main Product Keywords */}
                  <div className="input-container-box keywords-section">
                    <div className="labelcontainer">
                      <label htmlFor="mainProductKeywords">
                        <span className="required">*</span> Main Product Keywords
                      </label>
                    </div>
                    <div className="keywords-input-container">
                      <input
                        type="text"
                        className="form-control keywords-input"
                        id="mainProductKeywords"
                        name="mainProductKeywords"
                        placeholder="Enter keywords"
                        value={vendorData.mainProductKeywords}
                        onChange={handleChange}
                        maxLength="50"
                      />
                      <span className="character-count">50</span>
                      <input
                        type="text"
                        className="form-control keywords-input ml-2"
                        placeholder="Enter keywords"
                        maxLength="50"
                      />
                      <span className="character-count">50</span>
                    </div>
                    <button type="button" className="add-keywords-btn">
                      + Add More Keywords
                    </button>
                  </div>

                  {/* Company Description */}
                  <div className="input-container-box description-section">
                    <div className="labelcontainer">
                      <label htmlFor="companyDescription">
                        <span className="required">*</span> Company Description
                      </label>
                    </div>
                    <div className="rich-text-editor">
                      <div className="editor-toolbar">
                        <button type="button" className="toolbar-btn bold-btn">B</button>
                        <button type="button" className="toolbar-btn italic-btn">I</button>
                        <button type="button" className="toolbar-btn underline-btn">U</button>
                        <button type="button" className="toolbar-btn list-btn">≡</button>
                        <button type="button" className="toolbar-btn numbered-list-btn">1.</button>
                        <button type="button" className="toolbar-btn indent-btn">→</button>
                        <button type="button" className="toolbar-btn outdent-btn">←</button>
                        <button type="button" className="toolbar-btn undo-btn">↶</button>
                        <button type="button" className="toolbar-btn redo-btn">↷</button>
                      </div>
                      <textarea
                        className="form-control editor-content"
                        id="companyDescription"
                        name="companyDescription"
                        placeholder="请输入内容"
                        value={vendorData.companyDescription}
                        onChange={handleChange}
                        rows="8"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Business Profile Section */}
              <div className='userinfo-container' id="business-section">
                <div className='basic-content'>
                  <h3>Business Profile</h3>
                </div>

                <div className="form-container1">
                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="businessName">Company Name:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="businessName"
                      name="businessName"
                      placeholder='Enter Company Name'
                      value={vendorData.businessName}
                      onChange={handleChange}
                      required
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="OfficeContact">Office Contact Number:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="OfficeContact"
                      name="OfficeContact"
                      placeholder='Office Contact Number'
                      value={vendorData.OfficeContact}
                      onChange={handleChange}
                      required
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="FaxNumber">Fax Number:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="FaxNumber"
                      name="FaxNumber"
                      placeholder='Fax Number'
                      value={vendorData.FaxNumber}
                      onChange={handleChange}
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="Ownership">Ownership Type:</label>
                    </div>
                    <select
                      className="form-control"
                      id="Ownership"
                      name="Ownership"
                      value={vendorData.Ownership}
                      onChange={handleChange}
                      required
                    >
                      <option>Ownership Type</option>
                      <option>Public Limited Company</option>
                      <option>Private Limited Company</option>
                      <option>Partnership</option>
                      <option>Proprietorship</option>
                      <option>Professional Associations</option>
                      <option>Others</option>
                    </select>
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="AnnualTakeover">Annual Takeover</label>
                    </div>
                    <input
                      type="tel"
                      className="form-control"
                      id="AnnualTakeover"
                      name="AnnualTakeover"
                      placeholder='Annual Takeover'
                      value={vendorData.AnnualTakeover}
                      onChange={handleChange}
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="establishment">Year of Establishment:</label>
                    </div>
                    <input
                      type="tel"
                      className="form-control"
                      id="establishment"
                      name="establishment"
                      placeholder='Year of Establishment'
                      value={vendorData.establishment}
                      onChange={handleChange}
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="NoEmployee">Number of Employees:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="NoEmployee"
                      name="NoEmployee"
                      placeholder='Number of Employees'
                      value={vendorData.NoEmployee}
                      onChange={handleChange}
                      required
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer mb-3">
                      <label htmlFor="selectType">Select Type:</label>
                    </div>
                    <select
                      className="form-control"
                      id="selectType"
                      name="selectType"
                      value={vendorData.selectType}
                      onChange={handleChange}
                      required
                    >
                      <option>Service Based Company</option>
                      <option>Product Based Company</option>
                    </select>
                  </div>
                </div>
              </div>

              {/* Address Details Section */}
              <div className='userinfo-container mb-5' id="address-section">
                <div className='basic-content'>
                  <h3>Address Details</h3>
                </div>

                <div className="form-container1">
                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="Address">Address:</label>
                    </div>
                    <textarea
                      className="form-control"
                      id="Address"
                      name="Address"
                      placeholder="Enter Address"
                      value={vendorData.Address}
                      onChange={handleChange}
                      required
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="City">City:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="City"
                      name="City"
                      placeholder='City'
                      value={vendorData.City}
                      onChange={handleChange}
                      required
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="State">State:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="State"
                      name="State"
                      placeholder='State'
                      value={vendorData.State}
                      onChange={handleChange}
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="Pincode">Pincode:</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="Pincode"
                      name="Pincode"
                      placeholder='Pincode'
                      value={vendorData.Pincode}
                      onChange={handleChange}
                    />
                  </div>

                  <div className="input-container-box">
                    <div className="labelcontainer">
                      <label htmlFor="Country">Country</label>
                    </div>
                    <input
                      type="text"
                      className="form-control"
                      id="Country"
                      name="Country"
                      placeholder='Country'
                      value={vendorData.Country}
                      onChange={handleChange}
                      required
                    />
                  </div>
                </div>
              </div>

              {/* Sidebar Navigation */}
              <div className='sidecontent'>
                <div className='ant-anchor'>
                  <div className="side-line">
                    <span className='ant-anchor-ink-ball visible'></span>
                  </div>
                  <h5 className={`sidecontent-head ${activeSection === 'basic' ? 'active' : ''}`}>
                    Basic Company Information
                  </h5>
                  <h5 className={`sidecontent-head ${activeSection === 'business' ? 'active' : ''}`}>
                    Business details
                  </h5>
                  <h5 className={`sidecontent-head ${activeSection === 'address' ? 'active' : ''}`}>
                    Address details
                  </h5>
                </div>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="button-container mt-3">
              <button type="submit" className="btn btn-danger btn-submit">Submit</button>
              <button type="button" className="btn btn-secondary btn-draft ml-2">Save draft</button>
              <button type="button" className="btn btn-outline-secondary btn-cancel ml-2">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default UpdateProfileVendor;