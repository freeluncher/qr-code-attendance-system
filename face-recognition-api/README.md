# Face Recognition API

FastAPI-based service for face detection, landmarks, and verification.

## Setup

### Windows
```bash
start.bat
```

### Linux/Mac
```bash
chmod +x start.sh
./start.sh
```

### Manual Setup
```bash
# Create virtual environment
python -m venv venv

# Activate virtual environment
# Windows:
venv\Scripts\activate
# Linux/Mac:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Run server
python main.py
```

## API Endpoints

### 1. Health Check
- **GET** `/`
- Returns API status

### 2. Detect Face
- **POST** `/detect-face`
- Upload: `file` (image)
- Returns: Face landmarks, location, and quality metrics

### 3. Register Face Reference
- **POST** `/register-face`
- Upload: `file` (image)
- Params: `user_id` (integer)
- Saves reference face encoding for user

### 4. Verify Face
- **POST** `/verify-face`
- Upload: `file` (image)
- Params: `user_id` (integer)
- Returns: Face match result with confidence score

## Usage

The API runs on `http://localhost:8001` by default.

## Requirements

- Python 3.8+
- dlib
- face_recognition
- FastAPI
- OpenCV

## Directory Structure

```
face-recognition-api/
├── main.py              # FastAPI application
├── requirements.txt     # Python dependencies
├── start.bat           # Windows startup script
├── start.sh            # Linux/Mac startup script
├── reference_faces/    # Stored reference faces (auto-created)
└── temp_faces/         # Temporary processing (auto-created)
```
