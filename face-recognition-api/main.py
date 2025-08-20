from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
import face_recognition
import numpy as np
import cv2
from PIL import Image
import io
import base64
import os
from typing import Optional
import json

app = FastAPI(title="Face Recognition API", version="1.0.0")

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, specify exact origins
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Create directories for storing reference faces
os.makedirs("reference_faces", exist_ok=True)
os.makedirs("temp_faces", exist_ok=True)

@app.get("/")
async def root():
    return {"message": "Face Recognition API is running"}

@app.post("/detect-face")
async def detect_face(file: UploadFile = File(...)):
    """
    Detect face landmarks in uploaded image
    Returns face landmarks and basic validation
    """
    try:
        # Read image
        contents = await file.read()
        image = Image.open(io.BytesIO(contents))
        image_array = np.array(image)
        
        # Convert RGB to BGR for OpenCV
        if len(image_array.shape) == 3:
            image_array = cv2.cvtColor(image_array, cv2.COLOR_RGB2BGR)
        
        # Detect faces
        face_locations = face_recognition.face_locations(image_array)
        face_landmarks_list = face_recognition.face_landmarks(image_array)
        
        if len(face_locations) == 0:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "No face detected in the image",
                    "face_count": 0
                }
            )
        
        if len(face_locations) > 1:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Multiple faces detected. Please ensure only one person is in the photo",
                    "face_count": len(face_locations)
                }
            )
        
        # Get face encoding
        face_encodings = face_recognition.face_encodings(image_array, face_locations)
        
        if len(face_encodings) == 0:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Unable to encode face. Please ensure face is clear and well-lit",
                    "face_count": len(face_locations)
                }
            )
        
        face_encoding = face_encodings[0]
        face_landmarks = face_landmarks_list[0] if face_landmarks_list else {}
        
        # Basic face quality checks
        face_top, face_right, face_bottom, face_left = face_locations[0]
        face_width = face_right - face_left
        face_height = face_bottom - face_top
        
        # Check if face is too small (less than 100x100 pixels)
        if face_width < 100 or face_height < 100:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Face too small. Please move closer to the camera",
                    "face_count": 1
                }
            )
        
        return {
            "success": True,
            "message": "Face detected successfully",
            "face_count": 1,
            "face_location": {
                "top": int(face_top),
                "right": int(face_right),
                "bottom": int(face_bottom),
                "left": int(face_left)
            },
            "face_landmarks": {
                "chin": len(face_landmarks.get('chin', [])),
                "left_eyebrow": len(face_landmarks.get('left_eyebrow', [])),
                "right_eyebrow": len(face_landmarks.get('right_eyebrow', [])),
                "nose_bridge": len(face_landmarks.get('nose_bridge', [])),
                "nose_tip": len(face_landmarks.get('nose_tip', [])),
                "left_eye": len(face_landmarks.get('left_eye', [])),
                "right_eye": len(face_landmarks.get('right_eye', []))
            },
            "face_encoding": face_encoding.tolist(),
            "face_quality": {
                "width": face_width,
                "height": face_height,
                "area": face_width * face_height
            }
        }
        
    except Exception as e:
        return JSONResponse(
            status_code=500,
            content={
                "success": False,
                "message": f"Error processing image: {str(e)}"
            }
        )

@app.post("/register-face")
async def register_face(user_id: int, file: UploadFile = File(...)):
    """
    Register a reference face for a user
    """
    try:
        # First detect face
        contents = await file.read()
        image = Image.open(io.BytesIO(contents))
        image_array = np.array(image)
        
        if len(image_array.shape) == 3:
            image_array = cv2.cvtColor(image_array, cv2.COLOR_RGB2BGR)
        
        face_locations = face_recognition.face_locations(image_array)
        face_encodings = face_recognition.face_encodings(image_array, face_locations)
        
        if len(face_locations) != 1:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Please ensure exactly one face is visible in the photo"
                }
            )
        
        if len(face_encodings) == 0:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Unable to process face. Please ensure good lighting and clear image"
                }
            )
        
        # Save reference face encoding
        face_encoding = face_encodings[0]
        reference_path = f"reference_faces/user_{user_id}.json"
        
        reference_data = {
            "user_id": user_id,
            "face_encoding": face_encoding.tolist(),
            "registered_at": str(np.datetime64('now'))
        }
        
        with open(reference_path, 'w') as f:
            json.dump(reference_data, f)
        
        # Save reference image
        image.save(f"reference_faces/user_{user_id}.jpg")
        
        return {
            "success": True,
            "message": "Reference face registered successfully",
            "user_id": user_id
        }
        
    except Exception as e:
        return JSONResponse(
            status_code=500,
            content={
                "success": False,
                "message": f"Error registering face: {str(e)}"
            }
        )

@app.post("/verify-face")
async def verify_face(user_id: int, file: UploadFile = File(...)):
    """
    Verify uploaded face against registered reference
    """
    try:
        reference_path = f"reference_faces/user_{user_id}.json"
        
        if not os.path.exists(reference_path):
            return JSONResponse(
                status_code=404,
                content={
                    "success": False,
                    "message": "No reference face found for this user. Please register first"
                }
            )
        
        # Load reference face encoding
        with open(reference_path, 'r') as f:
            reference_data = json.load(f)
        
        reference_encoding = np.array(reference_data['face_encoding'])
        
        # Process current image
        contents = await file.read()
        image = Image.open(io.BytesIO(contents))
        image_array = np.array(image)
        
        if len(image_array.shape) == 3:
            image_array = cv2.cvtColor(image_array, cv2.COLOR_RGB2BGR)
        
        face_locations = face_recognition.face_locations(image_array)
        face_encodings = face_recognition.face_encodings(image_array, face_locations)
        
        if len(face_locations) != 1:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Please ensure exactly one face is visible in the photo",
                    "match": False
                }
            )
        
        if len(face_encodings) == 0:
            return JSONResponse(
                status_code=400,
                content={
                    "success": False,
                    "message": "Unable to process face. Please ensure good lighting and clear image",
                    "match": False
                }
            )
        
        current_encoding = face_encodings[0]
        
        # Compare faces
        face_distances = face_recognition.face_distance([reference_encoding], current_encoding)
        face_matches = face_recognition.compare_faces([reference_encoding], current_encoding, tolerance=0.6)
        
        distance = face_distances[0]
        is_match = face_matches[0]
        
        # Additional confidence calculation
        confidence = 1 - distance
        
        return {
            "success": True,
            "match": bool(is_match),
            "confidence": float(confidence),
            "distance": float(distance),
            "user_id": user_id,
            "message": "Face verified successfully" if is_match else "Face does not match reference"
        }
        
    except Exception as e:
        return JSONResponse(
            status_code=500,
            content={
                "success": False,
                "message": f"Error verifying face: {str(e)}",
                "match": False
            }
        )

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
