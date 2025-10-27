import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link, useParams, useNavigate } from 'react-router-dom';
import { FaUpload, FaTimes } from 'react-icons/fa';
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
    logo: '',
    AnnualTakeover: '',
    establishment: '',
    NoEmployee: '',
    selectType: '',
    Address: '',
    City: '',
    State: '',
    Country: '',
    Pincode: '',
    businessDetails: [],
  });
  
  // Added state for image handling
  const [images, setImages] = useState([]);
  const [imagePreviews, setImagePreviews] = useState([]);
  const [logoFile, setLogoFile] = useState(null);
  
  const [error, setError] = useState(null);
  const [activeSubMenu, setActiveSubMenu] = useState(null);
  const [businessType, setBusinessType] = useState(null);
  const [profileCompleteness, setProfileCompleteness] = useState(0);
  const [showCompanyDetails, setShowCompanyDetails] = useState(true);
  const [showAddressDetails, setShowAddressDetails] = useState(false);
  const [activeSection, setActiveSection] = useState('basic');
  
  const businessDetailsOptions = [
    "Agent",
    "Buying Office",
    "Consultant",
    "Distributor",
    "Domestic Seller",
    "Exporter",
    "Importer",
    "Manufacturer",
    "Online Seller",
    "Retailer",
    "Trading Company",
    "Wholesaler",
    "Other",
    "Fabricator"
  ];

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

  // Handle checkbox toggle for Business Details
  const handleCheckboxChange = (option) => {
    const currentBusinessDetails = vendorData.businessDetails || [];
    let updated = [];
    if (currentBusinessDetails.includes(option)) {
      updated = currentBusinessDetails.filter((item) => item !== option);
    } else {
      updated = [...currentBusinessDetails, option];
    }
    setVendorData({ ...vendorData, businessDetails: updated });
  };

  // Handle logo upload
  const handleLogoUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
      // Validate file type
      const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        setError('Please upload only PNG, JPG, or GIF files');
        return;
      }

      // Validate file size (10MB)
      if (file.size > 10 * 1024 * 1024) {
        setError('File size should be less than 10MB');
        return;
      }

      setLogoFile(file);
      
      // Create preview
      const reader = new FileReader();
      reader.onload = (e) => {
        setVendorData(prev => ({ ...prev, logo: e.target.result }));
      };
      reader.readAsDataURL(file);
    }
  };

  // Handle multiple image uploads
  const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    const remainingSlots = 10 - images.length;
    const filesToProcess = files.slice(0, remainingSlots);

    filesToProcess.forEach(file => {
      // Validate file type
      const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        setError('Please upload only PNG, JPG, or GIF files');
        return;
      }

      // Validate file size (10MB)
      if (file.size > 10 * 1024 * 1024) {
        setError('File size should be less than 10MB');
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        setImages(prev => [...prev, file]);
        setImagePreviews(prev => [...prev, e.target.result]);
      };
      reader.readAsDataURL(file);
    });
  };

  // Remove image
  const removeImage = (index) => {
    setImages(prev => prev.filter((_, i) => i !== index));
    setImagePreviews(prev => prev.filter((_, i) => i !== index));
  };

  useEffect(() => {
    const vendortoken = window.localStorage.getItem('vendortoken');

    if (!vendortoken) {
      setError('No token found');
      return;
    }

    axios.post(`${process.env.REACT_APP_API_URL}/vendorData`, { vendortoken })
      .then(response => {
        if (response.data.status === 'ok') {
          const responseData = {
            ...response.data.data,
            businessDetails: response.data.data.businessDetails || []
          };
          setVendorData(responseData);
          setBusinessType(response.data.data.businessType);
          
          // Set logo preview if exists
          if (response.data.data.logo) {
            setImagePreviews([response.data.data.logo]);
          }
        } else {
          setError(response.data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        setError(error.message);
      });
  }, [vendorId]);

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

  const handleSubmit = async (e) => {
    e.preventDefault();
    const vendortoken = window.localStorage.getItem('vendortoken');
    
    try {
      // Create FormData for file upload
      const formData = new FormData();
      
      // Append all vendor data
      Object.keys(vendorData).forEach(key => {
        if (key === 'businessDetails') {
          formData.append(key, JSON.stringify(vendorData[key]));
        } else {
          formData.append(key, vendorData[key]);
        }
      });

      // Append logo file if exists
      if (logoFile) {
        formData.append('logo', logoFile);
      }

      // Append additional images
      images.forEach((image, index) => {
        formData.append(`image_${index}`, image);
      });

      const response = await axios.put(
        `${process.env.REACT_APP_API_URL}/BusinessProfile`, 
        formData,
        {
          headers: { 
            'Authorization': `Bearer ${vendortoken}`,
            'Content-Type': 'multipart/form-data'
          }
        }
      );

      if (response.data.status === 'ok') {
        navigate('/');
      } else {
        setError(response.data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      setError(error.message);
    }
  };

  return (
    <div className="">
      <Navbar />
      <div className='main-container-businessDetails'>
        <VendorSidebar />
        <form onSubmit={handleSubmit} className='formbusiness'>
          <div className='info-layout'>
            {error && <p className="error">{error}</p>}
            <div className='business-sidecontent'>
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

                    {/* Logo Upload Section */}
                    <div className="input-container-box">
                      <div className="labelcontainer">
                        <label>Company Logo</label>
                      </div>
                      <div className="image-upload-area">
                        <div className="upload-grid">
                          {vendorData.logo && (
                            <div className="image-preview-card">
                              <img src={vendorData.logo} alt="Company Logo" />
                              <button 
                                type="button" 
                                className="remove-image-btn"
                                onClick={() => setVendorData(prev => ({ ...prev, logo: '' }))}
                              >
                                <FaTimes />
                              </button>
                            </div>
                          )}
                          
                          {!vendorData.logo && (
                            <div className="upload-placeholder">
                              <input
                                type="file"
                                accept="image/*"
                                onChange={handleLogoUpload}
                                className="hidden-file-input"
                                id="logo-upload"
                              />
                              <label htmlFor="logo-upload" className="upload-label">
                                <FaUpload className="upload-icon" />
                                <span>Upload Logo</span>
                                <small>PNG, JPG, GIF up to 10MB</small>
                              </label>
                            </div>
                          )}
                        </div>
                      </div>
                    </div>

                                         {/* Business Details Checkboxes */}
                     <div className="input-container-box">
                       <div className="labelcontainer mb-3">
                         <label className="form-label">Business Type</label>
                       </div>
                       <div className="form-group row mb-2">
                         {businessDetailsOptions.map((option) => (
                           <div className="form-check col-sm-2" key={option}>
                             <input
                               className="form-check-input"
                               type="checkbox"
                               value={option}
                               checked={vendorData.businessDetails && vendorData.businessDetails.includes(option)}
                               onChange={() => handleCheckboxChange(option)}
                               id={`check-${option}`}
                             />
                             <label className="form-check-label" htmlFor={`check-${option}`}>
                               {option}
                             </label>
                           </div>
                         ))}
                       </div>
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
                          placeholder="Description"
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
              </div>

              <div className='sidecontent'>
                <div className='sidecontent-inner'>
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