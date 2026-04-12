from flask import Flask, request, jsonify
from flask_cors import CORS
from dotenv import load_dotenv

load_dotenv()
app = Flask(__name__)
CORS(app)

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "Flask is running ✅"})

@app.route('/ask', methods=['POST'])
def ask():
    data = request.json
    question = data.get('question', '')
    # RAG logic goes here later
    return jsonify({"answer": f"Received: {question}"})

if __name__ == '__main__':
    app.run(debug=True, port=5000)